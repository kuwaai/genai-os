import os
import sys
import logging
import json
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
import openai
import tiktoken

from kuwa.executor import LLMWorker

logger = logging.getLogger(__name__)

# Updated 2024/04/01
CONTEXT_WINDOW = {
    ("gpt-3.5-turbo", "gpt-3.5-turbo-1106", "gpt-3.5-turbo-0125"): 16384,
    ("gpt-4", "gpt-4-0613"): 8192,
    ("gpt-4-32k", "gpt-4-32k-0613"): 32768,
    ("gpt-4-0125-preview", "gpt-4-turbo-preview", "gpt-4-1106-preview", "gpt-4-vision-preview", "gpt-4-1106-vision-preview"): 128000,
}

class ChatGptWorker(LLMWorker):

    model_name: str = "gpt-3.5-turbo"
    temperature: float = 0.5
    context_window: int = 0

    def __init__(self):
        super().__init__()

    def _create_parser(self):
        parser = super()._create_parser()
        parser.add_argument('--api_key', default=None, help='ChatGPT key from OpenAI')
        parser.add_argument('--model', default=self.model_name, help='Model name. See https://platform.openai.com/docs/models/overview')
        parser.add_argument('--temperature', default=self.temperature, help='What sampling temperature to use, between 0 and 2. Higher values like 0.8 will make the output more random, while lower values like 0.2 will make it more focused and deterministic.')
        return parser

    def _setup(self):
        super()._setup()

        self.model_name = self.args.model
        self.temperature = self.args.temperature
        if not self.LLM_name:
            self.LLM_name = "chatgpt"
        
        context_window = [v for k, v in CONTEXT_WINDOW.items() if self.model_name in k]
        if len(context_window) == 0:
            logging.warning(f"The context window length of model {self.model_name} not found. Set to minimal value.")
            self.context_window = min(CONTEXT_WINDOW.values())
        else:
            self.context_window = context_window[0]

        self.proc = False

    def num_tokens_from_messages(self, messages):
        """
        Return the number of tokens used by a list of messages.
        Reference: https://cookbook.openai.com/examples/how_to_count_tokens_with_tiktoken
        """
        try:
            encoding = tiktoken.encoding_for_model(self.model_name)
        except KeyError:
            logger.warning(f"Model {self.model_name} not found. Using cl100k_base encoding.")
            encoding = tiktoken.get_encoding("cl100k_base")
        
        # Fixed value for nowadays GPT-3.5/4
        tokens_per_message = 3
        tokens_per_name = 1
        
        num_tokens = 0
        for message in messages:
            num_tokens += tokens_per_message
            for key, value in message.items():
                num_tokens += len(encoding.encode(value))
                if key == "name":
                    num_tokens += tokens_per_name
        num_tokens += 3  # every reply is primed with <|start|>assistant<|message|>
        return num_tokens

    async def llm_compute(self, data):
        try:
            openai_token = data.get("openai_token") or self.args.api_key
            msg = [{"content":i['msg'], "role":"assistant" if i['isbot'] else "user"} for i in json.loads(data.get("input"))]
            
            if not msg or len(msg) == 0:
                yield "[沒有輸入任何訊息]"
                return
            
            if not openai_token or len(openai_token) == 0:
                yield "[請在網站的使用者設定中，將您的OpenAI API Token填入，才能使用該模型]"
                return

            # Trim the history to fit into the context window
            while self.num_tokens_from_messages(msg) > self.context_window:
                msg = msg[1:]
                if len(msg) == 0:
                    logging.debug("Aborted since the input message exceeds the limit.")
                    yield "[Sorry, The input message is too long!]"
                    return

            openai_token = openai_token.strip()
            openai.api_key = openai_token
            client = openai.AsyncOpenAI(api_key=openai_token)
            self.proc = True
            response = await client.chat.completions.create(
                model=self.model_name,
                temperature=self.temperature,
                messages=msg,
                stream=True
            )
            async for i in response:
                chunk = i.choices[0].delta.content
                if not self.proc: break
                if not chunk: continue

                if self.in_debug(): print(end=chunk, flush=True)
                yield chunk

            openai.api_key = None
        except Exception as e:
            logger.exception("Error occurs when calling OpenAI API")
            if str(e).startswith("Incorrect API key provided:"):
                yield "[無效的OpenAI API Token，請檢查該OpenAI API Token是否正確]"
            else:
                yield str(e)
        finally:
            self.proc = False
            logger.debug("finished")

    async def abort(self):
        if self.proc:
            self.proc = False
            logger.debug("aborted")
            return "Aborted"
        return "No process to abort"

if __name__ == "__main__":
    worker = ChatGptWorker()
    worker.run()