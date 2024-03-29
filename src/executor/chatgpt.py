import os
import sys
import logging
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
import openai

from framework import LLMWorker

logger = logging.getLogger(__name__)

class ChatGptWorker(LLMWorker):
    def __init__(self):
        super().__init__()

    def _create_parser(self):
        parser = super()._create_parser()
        parser.add_argument('--api_key', default=None, help='ChatGPT key from OpenAI')
        return parser

    def _setup(self):
        super()._setup()

        if not self.LLM_name:
            self.LLM_name = "chatgpt"

        self.proc = False

    async def llm_compute(self, data):
        try:
            chatgpt_apitoken = data.get("chatgpt_apitoken")
            if not chatgpt_apitoken: chatgpt_apitoken = self.args.api_key
            msg = [{"content":i['msg'], "role":"assistant" if i['isbot'] else "user"} for i in eval(data.get("input").replace("true","True").replace("false","False"))]
            
            if msg and chatgpt_apitoken:
                chatgpt_apitoken = chatgpt_apitoken.strip()
                if len(msg) > 0 and len(chatgpt_apitoken) > 0:
                    openai.api_key = chatgpt_apitoken
                    self.proc = True
                    limit = 1000*3+512 - len(str(msg))
                    if limit < 256: limit = 1000*16 - len(str(msg))
                    if limit <= 1000*3+512:
                        client = openai.AsyncOpenAI(api_key=chatgpt_apitoken)
                        response = await client.chat.completions.create(
                            model="gpt-3.5-turbo",
                            max_tokens=limit,
                            temperature=0.5,
                            messages=msg,
                            stream=True
                        )
                        async for i in response:
                            if i.choices[0].delta.content:
                                if ("This model's maximum context length is" in i.choices[0].delta.content):
                                    limit = 1000*16 - len(str(msg))
                                    break
                                if self.debug: print(end=i.choices[0].delta.content, flush=True)
                                yield i.choices[0].delta.content
                            if not self.proc: break
                    if limit > 1000*3+512:
                        for i in openai.chat.completions.create(model="gpt-3.5-turbo-16k",
                            max_tokens=limit,
                            temperature=0.5,
                            messages=msg, stream=True):
                            if i.choices[0].delta.content:
                                yield i.choices[0].delta.content
                            if not self.proc: break
                    logger.debug(limit)
                    openai.api_key = None
                else:
                    yield "[請在網站的使用者設定中，將您的OpenAI API Token填入，才能使用該模型]" if len(msg) > 0 else "[沒有輸入任何訊息]"
            else:
                yield "[請在網站的使用者設定中，將您的OpenAI API Token填入，才能使用該模型]" if msg else "[沒有輸入任何訊息]"
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