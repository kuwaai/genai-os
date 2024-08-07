import os
import re
import json
import asyncio
import requests
import logging
from urllib.parse import urljoin

logger = logging.getLogger(__name__)

class KuwaClient:
    def __init__(self, base_url=None, kernel_base_url="http://localhost:9000", model=None, auth_token=None, limit: int = 3072):
        self.base_url = base_url if base_url is not None else os.environ.get("KUWA_BASE_URL", "http://localhost")
        self.kernel_base_url = kernel_base_url
        self.model = model
        self.auth_token = auth_token if auth_token is not None else os.environ.get("KUWA_API_KEY", None)
        self.limit = limit

    def is_too_long(self, chat_history: [dict]):
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
        llm = [executor for executor in reversed(resp.json()) if not re.match(r"(.*[-_b]qa.*|whisper|painter|tool/.*)", executor)]
        logger.debug(llm)
        llm.append(None)
        return llm[0]

    async def create_base_model(self, name: str, access_code:str, auth_token: str = None, order: int = None, version:str=None, description:str=None, system_prompt:str=None, react_btn:str=None):
        url = urljoin(self.base_url, "/api/user/create/base_model")
        auth_token = self.auth_token if self.auth_token is not None else auth_token
        headers = {
            "Content-Type": "application/json",
            "Authorization": f"Bearer {auth_token}",
        }
        request_body = {
            "name": name,
            "access_code": access_code,
            "order": order,
            "version": version,
            "description": description,
            "system_prompt": system_prompt,
            "react_btn": react_btn,
        }
        resp = requests.post(url, headers=headers, json=request_body)
        if not resp.ok:
            raise RuntimeError(f'Request failed with status {resp.status_code}, {resp.json()}')
        return resp.json()

    async def create_bot(self, llm_access_code:str, bot_name: str, auth_token: str = None, modelfile:str=None, react_btn:str=None, bot_description:str=None, visibility:int=3):
        url = urljoin(self.base_url, "/api/user/create/bot")
        auth_token = self.auth_token if self.auth_token is not None else auth_token
        headers = {
            "Content-Type": "application/json",
            "Authorization": f"Bearer {auth_token}",
        }
        request_body = {
            "llm_access_code": llm_access_code,
            "modelfile": modelfile,
            "react_btn": react_btn,
            "bot_name": bot_name,
            "bot_describe": bot_description,
            "visibility": visibility,
        }
        resp = requests.post(url, headers=headers, json=request_body)
        if not resp.ok:
            raise RuntimeError(f'Request failed with status {resp.status_code}, {resp.json()}')
        return resp.json()

    async def chat_complete(self, auth_token: str = None, messages: list = [], timeout=120, streaming=True):
        url = urljoin(self.base_url, "/v1.0/chat/completions")
        auth_token = self.auth_token if self.auth_token is not None else auth_token
        headers = {
            "Content-Type": "application/json",
            "Authorization": f"Bearer {auth_token}",
        }
        model = self.model if self.model is not None else await self.get_available_llm()
        logger.debug(f"Use model {model}")
        request_body = {
            "messages": messages,
            "model": model,
            "stream": streaming
        }

        with requests.post(url, headers=headers, json=request_body, stream=True, timeout=timeout) as resp:
            if not resp.ok:
                raise RuntimeError(f'Request failed with status {resp.status_code}')

            for line in resp.iter_lines(decode_unicode=True):
                logger.debug(line)
                if not streaming:
                    yield json.loads(line)["choices"][0]["message"]["content"]
                    continue

                if line == "data: [DONE]":
                    break
                elif line.startswith("data: "):
                    chunk = json.loads(line[len("data: "):])["choices"][0]["delta"]
                    if not chunk: continue
                    yield chunk["content"]