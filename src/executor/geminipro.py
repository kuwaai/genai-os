import re
import os
import sys
import asyncio
import logging
import json
import pprint
from textwrap import dedent
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
import google.generativeai as genai

from kuwa.executor import LLMWorker
from kuwa.executor.util import expose_function_parameter, read_config, merge_config, DescriptionParser

logger = logging.getLogger(__name__)

class GeminiDescParser(DescriptionParser):
    """
    Extract parameter description from google.generativeai.GenerationConfig.
    Ref: https://github.com/google/generative-ai-python/blob/3704fa8b1859c2ac8135cdd36df73429a7b27acc/google/generativeai/types/generation_types.py#L70
    """
    def __call__(self, doc:str, name:str) -> str:
        doc = dedent(doc[doc.find("Attributes:")+len("Attributes:"):]) + "\nEOF"
        match = re.search(rf"{name}[^:]*:([\s\S]+?)\n[^\s\n]", doc, re.MULTILINE)
        if match:
            description = match.group(1).replace('\n', '')
        else:
            description = None
        return description
class GeminiWorker(LLMWorker):

    model_name: str = "gemini-1.0-pro"
    limit: int = 30720
    generation_config: dict = {}

    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        model_group = parser.add_argument_group('Model Options')
        model_group.add_argument('--api_key', default=None, help='Gemini API key from Google Cloud Console')
        model_group.add_argument('--model', default=self.model_name, help='Model name. See https://ai.google.dev/models/gemini')
        model_group.add_argument('--limit', type=int, default=self.limit, help='The limit of the user prompt')

        gen_group = parser.add_argument_group('Generation Options', 'Generation options for Google AI API. See https://ai.google.dev/api/python/google/generativeai/GenerationConfig')
        gen_group.add_argument('-c', '--generation_config', default=None, help='The generation configuration in YAML or JSON format. This can be overridden by other command-line arguments.')
        self.generation_config = expose_function_parameter(
            function=genai.GenerationConfig,
            parser=gen_group,
            defaults=self.generation_config,
            desc_parser=GeminiDescParser()
        )

    def setup(self):
        self.model_name = self.args.model
        self.limit = self.args.limit
        if not self.LLM_name:
            self.LLM_name = "gemini-pro"

        # Setup generation config
        file_gconf = read_config(self.args.generation_config) if self.args.generation_config else {}
        arg_gconf = {
            k: getattr(self.args, k)
            for k, v in self.generation_config.items()
            if f"--{k}" in sys.argv
        }
        self.generation_config = merge_config(base=self.generation_config, top=file_gconf)
        self.generation_config = merge_config(base=self.generation_config, top=arg_gconf)

        logger.debug(f"Generation config:\n{pprint.pformat(self.generation_config, indent=2)}")

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
                },
                generation_config=genai.GenerationConfig(**self.generation_config)
            )
            async for resp in response:

                # Continue when there's no text is avalilable in the response
                if len(resp.candidates) == 0 or not resp.candidates[0].content.parts: continue
                
                chunk = resp.text
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