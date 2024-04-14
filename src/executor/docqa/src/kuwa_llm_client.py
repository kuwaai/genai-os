import re
import json
import asyncio
import requests
import logging
from urllib.parse import urljoin

logger = logging.getLogger(__name__)
class KuwaLlmClient:

    def __init__(self, base_url="http://localhost", kernel_base_url="http://localhost:9000", model=None, auth_token=None, limit:int=3072):
        self.base_url = base_url
        self.kernel_base_url = kernel_base_url
        self.model = model
        self.auth_token = auth_token
        self.limit = limit

    def is_too_long(self, chat_history:[dict]):
        """
        A heuristic method to estimate the tokens
        """
        return len(str(chat_history)) > self.limit

    async def get_available_llm(self):
        url = urljoin(self.kernel_base_url, "/v1.0/worker/list")
        
        loop = asyncio.get_running_loop()
        resp = await loop.run_in_executor(None, requests.get, url)
        if not resp.ok:
            return None
        llm = [executor for executor in reversed(resp.json()) if not re.match(r".*[-_b]qa.*", executor)]
        logger.debug(llm)
        llm.append(None)
        return llm[0]

    async def chat_complete(self, auth_token:str=None, messages:list=[], timeout=120):

        url = urljoin(self.base_url, "/v1.0/chat/completions")
        headers = {
            "Content-Type": "application/json",
            "Authorization": f"Bearer {self.auth_token if self.auth_token is not None else auth_token}",
        }
        model = self.model if self.model is not None else await self.get_available_llm()
        logger.debug(f"Use model {model}")
        request_body = {
            "messages": messages,
            "model": model,
        }

        with requests.post(url, headers=headers, json=request_body, stream=True, timeout=timeout) as resp:
            if not resp.ok:
                raise RuntimeError(f'Request failed with status {resp.status_code}')
            for line in resp.iter_lines(decode_unicode=True):
                if line == "event: close": break
                elif line.startswith("data: "):
                    chunk = json.loads(line[len("data: "):])["choices"][0]["delta"]["content"]
                    yield chunk