import os
import logging
import json
import aiohttp
import asyncio
from worker_framework.datatype import ChatRecord, Role

logger = logging.getLogger(__name__)

class KuwaChatApiClient:
    def __init__(
        self,
        api_token:str,
        api_endpoint:str = "https://chatdev.gai.tw/v1.0/chat/completions"
        ):
        self.api_endpoint = api_endpoint
        self.api_token = api_token

    async def chat_completion(self, bot_id:str, messages: [ChatRecord]):

        messages = self.convert_chat_history(messages)

        # Define the API endpoint and authentication headers.
        headers = {
            'Content-Type': 'application/json',
            'Authorization': f'Bearer {self.api_token}'
        }

        logger.debug("------------------")
        logger.debug(f"Invoking bot: {bot_id}")
        logger.debug(f"Input messages: {messages}")
        logger.debug("------------------")

        request_data = {
            "messages": messages,
            "model": bot_id
        }
        
        # Perform the HTTP request.
        request_arg = dict(
            headers=headers,
            json=request_data,
            timeout=60
        )
        async with aiohttp.ClientSession() as session:
            async with session.post(self.api_endpoint, **request_arg) as resp:
                if not resp.ok:
                    raise RuntimeError(f'Request failed with status {resp.status}')

                async for line in resp.content:
                    line = line.decode('utf8')
                    logger.debug(f"Received: {line}")
                    finished, chunk = self.parse_sse_chunk(line)
                    if finished: break
                    yield chunk

    def convert_chat_history(self, chat_history: [ChatRecord]) -> [dict]:
        return [
            {
                'isbot': bool(record.role != Role.USER),
                'msg': record.msg
            }
            for record in chat_history
        ]
    
    def parse_sse_chunk(self, raw_line) -> (bool, str | None):
        finished = False
        chunk = None
        if not raw_line or raw_line == "event: end":
            finished = True
        elif raw_line.startswith("data: "):
            chunk = json.loads(raw_line[len("data: "):])["choices"][0]["delta"]["content"]
        return finished, chunk

if __name__ == "__main__":
    api_token = os.environ['KUWA_TOKEN']
    bot_id = "gemma-7b-instruct"
    messages = [
        ChatRecord(role=Role.USER, msg="你好")
    ]
    logging.basicConfig(level=logging.INFO)

    client = KuwaChatApiClient(api_token=api_token)

    async def consumer():
        async for chunk in client.chat_completion(bot_id=bot_id, messages=messages):
            print(chunk, end='', flush=True)

    asyncio.run(consumer())

