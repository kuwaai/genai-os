import asyncio
import requests
import functools
import logging
from os import environ
from oauthlib.oauth2 import LegacyApplicationClient
from requests_oauthlib import OAuth2Session

from .taide_llm import TaideLlm

logger = logging.getLogger(__name__)

class NchcTaideAuth(requests.auth.AuthBase):
    def __init__(self,
                 username:str,
                 password:str,
                 client_id:str = 'python-client',
                 api_root = 'https://td.nchc.org.tw/api/v1'
                ):
        self.token_endpoint = f'{api_root}/token'
        self.client_id = client_id
        self.username = username
        self.password = password
        self.token = None
    
    def auth(self):
        oauth = OAuth2Session(client=LegacyApplicationClient(client_id=self.client_id))
        token = None
        try:
            token = oauth.fetch_token(
                token_url=self.token_endpoint,
                username=self.username,
                password=self.password,
                client_id=self.client_id
            )['access_token']
            logger.debug('Authenticated')
        except Exception as e:
            logger.exception('Authentication failed.')
            raise e
        finally:
            self.token = token
    
    def __call__(self, r):
        assert self.token != None
        r.headers["authorization"] = "Bearer " + self.token
        return r

class NchcTaideLlm(TaideLlm):
    def __init__(self,
                 model_name = 'TAIDE/b.5.0.0',
                 client_id = 'doc_qa',
                 api_root = 'https://td.nchc.org.tw/api/v1',
                 ):
        super(NchcTaideLlm, self).__init__()

        assert 'NCHC_TAIDE_USERNAME' in environ, 'Environment variable "NCHC_TAIDE_USERNAME" is not set to use the NCHC TAIDE API.'
        assert 'NCHC_TAIDE_PASSWORD' in environ, 'Environment variable "NCHC_TAIDE_PASSWORD" is not set to use the NCHC TAIDE API.'

        self.auth = NchcTaideAuth(
            username = environ['NCHC_TAIDE_USERNAME'],
            password = environ['NCHC_TAIDE_PASSWORD'],
            client_id = client_id,
            api_root = api_root
        )
        self.api_root = api_root
        self.model_name = model_name

    async def _complete(self, prompt:str)-> (str, int):
        result = ''
        output_tokens = 0
        try:
            loop = asyncio.get_running_loop()
            await loop.run_in_executor(None, self.auth.auth)
            
            llm_endpoint = f'{self.api_root}/completions'
            data = {
                'max_tokens': 2048,
                "model": self.model_name,
                'prompt': prompt,
                'temperature': 0.2,
                'top_p': 0.95
            }
            resp = await loop.run_in_executor(
                None,
                functools.partial(
                    requests.post,
                    llm_endpoint,
                    json=data,
                    auth=self.auth
                )
            )
            if not resp.ok:
                raise RuntimeError(f'Failed to invoke LLM API. Response status code {resp.status_code}')
            
            resp = resp.json()
            reply = resp["choices"][0]["text"]
            prompt_end = "[/INST]"
            result = reply[reply.rfind(prompt_end)+len(prompt_end):].strip()
            output_tokens = resp['usage']['completion_tokens']
            
        except Exception as e:
            result = ''
            logger.exception('Generation failed.')
        finally:
            return result, output_tokens