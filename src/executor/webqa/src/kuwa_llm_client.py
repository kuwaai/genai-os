import json

class KuwaLlmClient:

    def __init__(self, base_url="http://localhost", model="gemini-pro", auth_token=None):
        self.base_url = base_url
        self.model = model
        self.auth_token = auth_token

    async def invoke_model(self, auth_token:str=None, messages:list=[]):

        url = f"{base_url}/v1.0/chat/completions"
        headers = {
            "Content-Type": "application/json",
            "Authorization": f"Bearer {auth_token if auth_token is not None else self.auth_token}",
        }
        request_body = {
            "messages": messages,
            # "messages": [{"isbot": False, "msg": prompt,}],
            "model": model,
        }

        with requests.post(url, headers=headers, json=request_body, stream=True, timeout=60) as resp:
            if not resp.ok:
                raise RuntimeError(f'Request failed with status {response.status_code}')
            for line in resp.iter_lines(decode_unicode=True):
                if not line or line == "event: end": break
                elif line.startswith("data: "):
                    chunk = json.loads(line[len("data: "):])["choices"][0]["delta"]["content"]
                    yield chunk