import asyncio
import requests
import functools
import chevron
import logging
from pathlib import Path
from os import environ

from .taide_llm import TaideLlm,ChatTuple

logger = logging.getLogger(__name__)

class LLMProjectTaideLlm(TaideLlm):
    def __init__(self,
                     token_limit = 3000,
                     prompt_template_path = 'prompt_template/taide.mustache',
                     debug = True
                     ):
            self.input_token_limit = token_limit
            self.debug = debug
            prompt_template_file = Path(prompt_template_path)
            self.prompt_template = prompt_template_file.read_text(encoding="utf8")

    def _is_too_long(self, sentence: list) -> bool:
        num_tokens = len("".join([i["msg"] for i in sentence]))
        return num_tokens >= self.input_token_limit, num_tokens
    def api(self, msg):
        # Define the API endpoint and authentication headers.
        api_url = 'http://127.0.0.1/v1.0/chat/completions'
        headers = {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + environ['taide_api_token']
        }
        filtered = []
        flag = False
        for i in msg:
            if i["isbot"] == str(flag).lower():
                filtered += [i]
                flag = not flag
            else:
                filtered[-1]["msg"] += i["msg"]
        if self.debug:
            print("------------------")
            print("Using access_code:", environ['access_code'])
            print("Input debug:", filtered)
            print("------------------")
        # Define the request payload as a dictionary for single round chatting.
        request_data = {
            "messages": filtered,
            "model": environ['access_code']
        }
        # Perform the HTTP request using the requests library.
        response = requests.post(api_url, headers=headers, json=request_data)
        print(response.json())
        if response.status_code == 200:
            data = response.json()
            # Handle the response data.
            #print(data)
            return data["output"]
        else:
            print(f'Error: Request failed with status {response.status_code}')
        raise Exception("Oops")
    def gen_prompt(self, chat_history: [ChatTuple], append_system: bool = True) -> list:
        """
        Generate prompt from given chat history.
        """

        system_chat_tuple = ChatTuple(
            user = '請用中文回答我',
            bot = '當然!為方便溝通,我使用的是傳統中文語言。您有何請求或疑問,請慷慨請教我?'
        )

        if append_system:
            chat_history = [system_chat_tuple] + chat_history
        prompt = []
        for i in chat_history:
            if i.user:
                prompt += [{"isbot":"false", "msg":i.user}]
            if i.bot:
                prompt += [{"isbot":"true", "msg":i.bot}]

        return prompt
        
    async def _complete(self, prompt:list, tokens:int)-> (str, int):
        result = ''
        output_tokens = 0
        try:
            loop = asyncio.get_running_loop()
            logger.debug(f'Data:\n{prompt}')

            result = self.api(prompt)
            logger.debug(f'Reply from API: {result}')
            
        except Exception as e:
            result = ''
            logger.exception('Generation failed.')
            raise
        finally:
            return result, output_tokens