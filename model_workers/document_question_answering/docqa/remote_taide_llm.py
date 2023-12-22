import asyncio
import requests
import functools
import logging
import json
from os import environ
from oauthlib.oauth2 import LegacyApplicationClient
from requests_oauthlib import OAuth2Session

from .taide_llm import TaideLlm
from worker_framework.datatype import ChatRecord, Role

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
        self.model_name = environ.get('NCHC_TAIDE_MODEL_NAME', 'TAIDE/b.5.0.0')

    async def _complete(self, prompt:str, tokens:int)-> (str, int):
        result = ''
        output_tokens = 0
        try:
            loop = asyncio.get_running_loop()
            await loop.run_in_executor(None, self.auth.auth)
            
            llm_endpoint = f'{self.api_root}/completions'
            data = {
                'max_tokens': 4096-tokens,
                "model": self.model_name,
                'prompt': prompt,
                'temperature': 0.2,
                'top_p': 0.95
            }
            logger.debug(f'Data:\n{data}')
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
            logger.debug(f'Reply from API: {reply}')
            prompt_end = "[/INST]"
            prompt_end_location = reply.rfind(prompt_end)
            if prompt_end_location != -1:
                result = reply[prompt_end_location+len(prompt_end):].strip()
            else:
                result = reply.strip()
            output_tokens = resp['usage']['completion_tokens']
            
        except Exception as e:
            result = ''
            logger.exception('Generation failed.')
            raise
        finally:
            return result, output_tokens

class LlmProjectTaideLlm(TaideLlm):
    def __init__(self):
        super(LlmProjectTaideLlm, self).__init__()
        self.api_endpoint = environ.get(
            'LLM_PROJECT_TAIDE_API_ENDPOINT',
            'https://chatdev.gai.tw/v1.0/chat/completions'
        )
        assert 'LLM_PROJECT_TAIDE_API_TOKEN' in environ, 'Environment variable "LLM_PROJECT_TAIDE_API_TOKEN" is not set to use the LLM_PROJECT TAIDE API.'
        assert 'LLM_PROJECT_TAIDE_ACCESS_CODE' in environ, 'Environment variable "LLM_PROJECT_TAIDE_ACCESS_CODE" is not set to use the LLM_PROJECT TAIDE API.'
        self.api_token = environ['LLM_PROJECT_TAIDE_API_TOKEN']
        self.model_access_code = environ['LLM_PROJECT_TAIDE_ACCESS_CODE']

    async def call_api(self, messages: [dict]):
        # Define the API endpoint and authentication headers.
        headers = {
            'Content-Type': 'application/json',
            'Authorization': f'Bearer {self.api_token}'
        }
        logger.debug("------------------")
        logger.debug(f"Using access_code: {self.model_access_code}")
        logger.debug(f"Input messages: {messages}")
        logger.debug("------------------")

        request_data = {
            "messages": messages,
            "model": self.model_access_code
        }
        
        # Perform the HTTP request.
        loop = asyncio.get_running_loop()
        response = await loop.run_in_executor(
            None,
            functools.partial(
                requests.post,
                self.api_endpoint,
                headers=headers,
                json=request_data
            )
        )
        logger.debug(response.json())
        if not response.ok:
            raise RuntimeError(f'Request failed with status {response.status_code}')
        
        data = response.json()
        return data["output"]

    async def complete(self, chat_history: [ChatRecord]) -> str:
        chat_history = [
            {
                'isbot': bool(record.role != Role.USER),
                'msg': record.msg
            }
            for record in chat_history
        ]
        
        result = ''
        try:
            result = await self.call_api(chat_history)
            logger.debug(f'Reply from API: {result}')
            return result
            
        except Exception as e:
            result = ''
            logger.exception('Generation failed.')
            raise
        
    async def _complete(self, prompt:list, tokens:int)-> (str, int):
        raise NotImplementedError()