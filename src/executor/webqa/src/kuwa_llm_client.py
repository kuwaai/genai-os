import json
import requests
from urllib.parse import urljoin

class KuwaLlmClient:

    def __init__(self, base_url="http://localhost", model="gemini-pro", auth_token=None, limit:int=30720):
        self.base_url = base_url
        self.model = model
        self.auth_token = auth_token
        self.limit = limit

    def is_too_long(self, chat_history:[dict]):
        """
        A heuristic method to estimate the tokens
        """
        return len(str(chat_history)) > self.limit

    async def chat_complete(self, auth_token:str=None, messages:list=[], timeout=60):

        url = urljoin(self.base_url, "/v1.0/chat/completions")
        headers = {
            "Content-Type": "application/json",
            "Authorization": f"Bearer {auth_token if auth_token is not None else self.auth_token}",
        }
        request_body = {
            "messages": messages,
            "model": self.model,
        }

        with requests.post(url, headers=headers, json=request_body, stream=True, timeout=timeout) as resp:
            if not resp.ok:
                raise RuntimeError(f'Request failed with status {resp.status_code}')
            for line in resp.iter_lines(decode_unicode=True):
                if line == "event: close": break
                elif line.startswith("data: "):
                    chunk = json.loads(line[len("data: "):])["choices"][0]["delta"]["content"]
                    yield chunk