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
    def __init__(self):
        super().__init__()

    def _create_parser(self):
        parser = super()._create_parser()
        parser.add_argument('--api_key', default=None, help='Gemini API key from Google Cloud Console')
        return parser

    def _setup(self):
        super()._setup()
        if not self.args.api_key:
            raise Exception("You need Gemini API key from Google Cloud Console to host Gemini Pro model! Please add by --api_key <your_key>")

        if not self.LLM_name:
            self.LLM_name = "gemini-pro"

        genai.configure(api_key=self.args.api_key)
        self.model = genai.GenerativeModel('gemini-pro')
        self.proc = False

    async def llm_compute(self, data):
        try:
            msg = [{"parts":[{"text":i['msg'].encode("utf-8",'ignore').decode("utf-8")}], "role":"model" if i['isbot'] else "user"} for i in json.loads(data.get("input"))]
            quiz = msg[-1]
            msg = msg[:-1]
            chat = self.model.start_chat(history=msg)
            self.proc = True
            response = await chat.send_message_async(quiz, stream=True,safety_settings={'HARASSMENT':'block_none','HARM_CATEGORY_DANGEROUS_CONTENT':'block_none','HARM_CATEGORY_HATE_SPEECH':'block_none',"HARM_CATEGORY_SEXUALLY_EXPLICIT":"block_none"})
            async for i in response:
                for o in i.text:
                    yield o
                    if self.in_debug(): print(end=o, flush=True)
                    await asyncio.sleep(0.01)
                    if not self.proc: break
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