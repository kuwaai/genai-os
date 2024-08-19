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

import mimetypes
import requests
import hashlib
import tempfile
import mimetypes
import inspect

from kuwa.executor import LLMExecutor, Modelfile
from kuwa.executor.llm_executor import extract_last_url, rectify_chat_history
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

class GoogleFileStore:
    """
    Upload files and delete through Gemini File API.
    """

    files = {} # Hashmap to cache uploaded files
    def __init__(self, google_token):
        genai.configure(api_key=google_token)
    
    async def upload_file(self, content, mime_type:str):
        name = hashlib.sha512(content).hexdigest()
        suffix = mimetypes.guess_extension(mime_type)
        try:
            # Shortcut for the cached and valid file
            if name in self.files and genai.get_file(name):
                return self.files[name]
        except:
            pass

        uploaded_file = None
        f = tempfile.NamedTemporaryFile(suffix=suffix, delete=False)
        f.write(content)
        f.flush()
        uploaded_file = genai.upload_file(path=f.name, display_name=name)
        logger.info(f"Uploaded file {name}")
        f.close()
        os.unlink(f.name)
        self.files[name] = uploaded_file
        await self.wait_file_active(uploaded_file)
        return uploaded_file

    async def wait_file_active(self, file, retry_second=2.0, backoff_factor=1.1):
        """
        Wait until the uploaded file in ACTIVE state.
        """
        while True:
            resp = genai.get_file(name=file.name)
            logger.info(f"Waiting file state become ACTIVE. Current: {resp.state}")
            if resp.state == genai.protos.File.State.ACTIVE:
                break
            await asyncio.sleep(retry_second)
            retry_second *= backoff_factor

    async def delete_all_files(self):
        if len(self.files) == 0:
            return
        logger.info(f"Cleaning uploaded files")
        for file in self.files.values():
            try:
                file.delete()
            except Exception as e:
                logger.exception("Error deleting file")
            finally:
                file = None
        self.files = filter(None, self.files)

class GeminiExecutor(LLMExecutor):

    model_name: str = "gemini-1.5-pro"
    system_prompt: str = ""
    no_system_prompt: bool = False
    limit: int = 30720
    generation_config: dict = {}

    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        model_group = parser.add_argument_group('Model Options')
        model_group.add_argument('--api_key', default=None, help='Gemini API key from Google Cloud Console')
        model_group.add_argument('--model', default=self.model_name, help='Model name. See https://ai.google.dev/models/gemini')
        model_group.add_argument('--limit', type=int, default=self.limit, help='The limit of the user prompt')
        model_group.add_argument('--system_prompt', default=self.system_prompt, help='The system prompt that is prepend to the chat history.')
        model_group.add_argument('--no_system_prompt', default=False, action='store_true', help='Disable the system prompt if the model doesn\'t support it.')
        model_group.add_argument('--multimodal', default=False, action='store_true', help='Activate multimodal functionalities.')
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
        self.system_prompt = self.args.system_prompt
        self.no_system_prompt = self.args.no_system_prompt

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

    def fetch_attachment(self, url: str):
        # Ref: https://cloud.google.com/vertex-ai/generative-ai/docs/model-reference/inference#blob
        supported_mime_types=[
            "application/pdf", "text/plain",
            "audio/mpeg", "audio/mp3", "audio/wav",
            "image/png", "image/jpeg",
            "video/mov", "video/mpeg", "video/mp4", "video/mpg",
            "video/avi", "video/wmv", "video/mpegps", "video/flv",
        ]

        mime_type = None
        content = None 
        if url is not None and url != "":
            mime_type = requests.head(url, allow_redirects=True).headers["content-type"]
            if mime_type in supported_mime_types:
                content = requests.get(url, stream=True, allow_redirects=True).content
            logger.info("Attachment fetched.")

        return mime_type, content

    async def parse_messages(self, msgs:[dict], enable_multimodal:bool, file_store:GoogleFileStore):
        """
        Parse multi-modal messages from chat history.
        """
        result = []
        for msg in msgs:
            new_msg = {
                    "parts":[],
                    "role": {"user": "user", "assistant": "model"}[msg["role"]]
                }
            if enable_multimodal:
                url, text = extract_last_url([msg])
                mime_type, file_content = self.fetch_attachment(url)
                new_msg["parts"].append({"text": text[0]["content"].encode("utf-8",'ignore').decode("utf-8")})
                if file_content is not None:
                    new_msg["parts"].append({
                        "file_data": {
                            "mime_type": mime_type,
                            "file_uri": (await file_store.upload_file(file_content, mime_type)).uri
                        }
                    })
            new_msg["parts"].append({"text": msg["content"].encode("utf-8",'ignore').decode("utf-8")})
            result.append(new_msg)

        return result 
    
    async def llm_compute(self, history: list[dict], modelfile:Modelfile):
        try:
            google_token = modelfile.parameters["_"].get("google_token") or self.args.api_key
            enable_multimodal = modelfile.parameters["llm_"].get("enable_multimodal", self.args.multimodal)
            model_name = modelfile.parameters["llm_"].get("model", self.model_name)
            file_store = GoogleFileStore(google_token)

            # Parse and process modelfile
            override_system_prompt = modelfile.override_system_prompt
            if not override_system_prompt: override_system_prompt = "" if self.no_system_prompt else self.system_prompt

            # Apply parsed modelfile data to Inference
            raw_inputs = modelfile.messages + history
            msg = await self.parse_messages(raw_inputs, enable_multimodal, file_store)
            msg[-1]["parts"][0]['text'] = modelfile.before_prompt + msg[-1]["parts"][0]['text'] + modelfile.after_prompt
            msg[0]["parts"][0]['text'] = override_system_prompt + msg[0]["parts"][0]['text']
            
            if not google_token or len(google_token) == 0:
                yield "[Please enter your Google API Token in the user settings of the website in order to use this model.]"
                return

            genai.configure(api_key=google_token)
            self.model = genai.GenerativeModel(model_name)

            # Trim the history to fit into the context window
            while await self.count_token(msg) > self.limit:
                msg = msg[1:]
                msg = rectify_chat_history(msg)
                if len(msg) == 0:
                    logger.debug("Aborted since the input message exceeds the limit.")
                    yield "[Sorry, The input message is too long!]"
                    return

            quiz = msg[-1]
            history = msg[:-1]
            logger.debug(f'msg: {msg}')
            chat = self.model.start_chat(history=history)
            self.proc = True
            generation_config = merge_config(self.generation_config, modelfile.parameters["llm_"])
            generation_config.pop('enable_multimodal', None)
            generation_config.pop('model', None)
            response = await chat.send_message_async(
                quiz,
                stream=True,
                safety_settings={
                    "HARASSMENT": "block_none",
                    "HARM_CATEGORY_DANGEROUS_CONTENT": "block_none",
                    "HARM_CATEGORY_HATE_SPEECH": "block_none",
                    "HARM_CATEGORY_SEXUALLY_EXPLICIT": "block_none"
                },
                generation_config=genai.GenerationConfig(
                    **generation_config
                )
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
            await file_store.delete_all_files()
            self.proc = False
            logger.debug("finished")

    async def abort(self):
        if self.proc:
            self.proc = False
            logger.debug("aborted")
            return "Aborted"
        return "No process to abort"

if __name__ == "__main__":
    executor = GeminiExecutor()
    executor.run()