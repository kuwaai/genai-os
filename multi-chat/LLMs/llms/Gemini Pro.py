import argparse
import os
import google.generativeai as genai
from base import *

class GeminiWorker(LLMWorker):
    def __init__(self):
        super().__init__()

    def _create_parser(self):
        parser = super()._create_parser()
        parser.add_argument('--gemini_key', default=None, help='Gemini API key from Google Cloud Console')
        return parser

    def _setup(self):
        super()._setup()

        if not self.args.gemini_key:
            raise Exception("You need Gemini API key from Google Cloud Console to host Gemini Pro model!")

        if not self.LLM_name:
            self.LLM_name = "gemini-pro"

        genai.configure(api_key=self.args.gemini_key)
        self.model = genai.GenerativeModel('gemini-pro')
        self.proc = False

    def llm_compute(self, data):
        try:
            msg = [{"parts":[{"text":i['msg'].encode("utf-8",'ignore').decode("utf-8")}], "role":"model" if i['isbot'] else "user"} for i in eval(data.get("input").replace("true","True").replace("false","False"))]
            quiz = msg[-1]
            msg = msg[:-1]
            chat = self.model.start_chat(history=msg)
            self.proc = True
            for i in chat.send_message(quiz, stream=True,safety_settings={'HARASSMENT':'block_none','HARM_CATEGORY_DANGEROUS_CONTENT':'block_none','HARM_CATEGORY_HATE_SPEECH':'block_none',"HARM_CATEGORY_SEXUALLY_EXPLICIT":"block_none"}):
                for o in i.text:
                    yield o
                    print(end=o)
                    time.sleep(0.01)
                    if not self.proc: break
                if not self.proc: break
        except Exception as e:
            print(e)
            yield str(e)
        finally:
            self.proc = False
            self.Ready = True
            print("finished")

    def abort(self):
        if self.proc:
            self.proc = False
            print("aborted")
            return "Aborted"
        return "No process to abort"

if __name__ == "__main__":
    worker = GeminiWorker()
    worker.run()
