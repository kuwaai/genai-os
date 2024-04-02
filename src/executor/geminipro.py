import os
import sys
import asyncio
import logging
import json
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
import google.generativeai as genai

from kuwa.executor import LLMWorker

logger = logging.getLogger(__name__)

class GeminiWorker(LLMWorker):

    model_name: str = "gemini-1.0-pro"
    limit: int = 30720

    def __init__(self):
        super().__init__()

    def _create_parser(self):
        parser = super()._create_parser()
        parser.add_argument('--api_key', default=None, help='Gemini API key from Google Cloud Console')
        parser.add_argument('--model', default=self.model_name, help='Model name. See https://ai.google.dev/models/gemini')
        parser.add_argument('--limit', type=int, default=self.limit, help='The limit of the user prompt')
        return parser

    def _setup(self):
        super()._setup()
        
        self.model_name = self.args.model
        self.limit = self.args.limit
        if not self.LLM_name:
            self.LLM_name = "gemini-pro"

        self.proc = False

    async def count_token(self, messages: list):
        contents = [m["parts"][0]["text"] for m in messages]
        check_resp = await self.model.count_tokens_async(contents=contents)
        return check_resp.total_tokens
    
    def rectify_history(self, messages: list):
        """
        Ensure the history begin with "user."
        """
        first_user_idx = 0
        while messages[first_user_idx]["role"] != "user":
            first_user_idx += 1
        messages = messages[first_user_idx:]
        return messages
    
    async def llm_compute(self, data):
        try:
            google_token = data.get("google_token") or self.args.api_key
            msg = [{"parts":[{"text":i['msg'].encode("utf-8",'ignore').decode("utf-8")}], "role":"model" if i['isbot'] else "user"} for i in json.loads(data.get("input"))]

            if not google_token or len(google_token) == 0:
                yield "[請在網站的使用者設定中，將您的Google API Token填入，才能使用該模型]"
                return

            genai.configure(api_key=google_token)
            self.model = genai.GenerativeModel(self.model_name)

            # Trim the history to fit into the context window
            while await self.count_token(msg) > self.limit:
                msg = msg[1:]
                msg = self.rectify_history(msg)
                if len(msg) == 0:
                    logging.debug("Aborted since the input message exceeds the limit.")
                    yield "[Sorry, The input message is too long!]"
                    return

            quiz = msg[-1]
            history = msg[:-1]
            chat = self.model.start_chat(history=history)
            self.proc = True
            response = await chat.send_message_async(
                quiz,
                stream=True,
                safety_settings={
                    "HARASSMENT": "block_none",
                    "HARM_CATEGORY_DANGEROUS_CONTENT": "block_none",
                    "HARM_CATEGORY_HATE_SPEECH": "block_none",
                    "HARM_CATEGORY_SEXUALLY_EXPLICIT": "block_none"
                }
            )
            async for i in response:
                chunk = i.text
                yield chunk
                if self.in_debug(): print(end=chunk, flush=True)
                if not self.proc: break
        except Exception as e:
            logger.exception("Error occurs when calling Gemini-Pro API.")
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
    worker = GeminiWorker()
    worker.run()