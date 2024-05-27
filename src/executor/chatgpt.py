import re
import os
import sys
import logging
import json
import typing
import pprint
from textwrap import dedent
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
import openai
import tiktoken
from openai.resources.chat.completions import AsyncCompletions

from kuwa.executor import LLMExecutor, Modelfile
from kuwa.executor.util import (
    expose_function_parameter,
    read_config,
    merge_config,
    DescriptionParser,
    to_openai_chat_format
)

logger = logging.getLogger(__name__)

# Updated 2024/05/16
CONTEXT_WINDOW = {
    ("gpt-3.5-turbo", "gpt-3.5-turbo-1106", "gpt-3.5-turbo-0125"): 16384,
    ("gpt-3.5-turbo-instruct",): 4096,
    ("gpt-4", "gpt-4-0613"): 8192,
    ("gpt-4-32k", "gpt-4-32k-0613"): 32768,
    ("gpt-4-0125-preview", "gpt-4-turbo-preview", "gpt-4-1106-preview", "gpt-4-vision-preview", "gpt-4-1106-vision-preview", "gpt-4-turbo", "gpt-4-turbo-2024-04-09", "gpt-4o", "gpt-4o-2024-05-13"): 128000,
}

class ChatGptDescParser(DescriptionParser):
    """
    Extract parameter description from openai.resources.chat.completions.AsyncCompletions.create.
    Ref: https://github.com/openai/openai-python/blob/f0bdef04611a24ed150d19c4d180aacab3052704/src/openai/resources/chat/completions.py#L97
    """
    def __call__(self, doc:str, name:str) -> str:
        """
        [TODO]
        Currently, this parser is not functioning properly because the "create"
        function is decorated with the @typing.overload decorator, which causes
        the docstring to be None.
        """
        if not doc: return None
        doc = dedent(doc[doc.find("Args:")+len("Args:"):])
        match = re.search(rf"{name}:([\s\S]+?)\n[^\s\n]", doc, re.MULTILINE)
        if match:
            description = match.group(1).replace('\n', '')
        else:
            description = None
        return description

class ChatGptExecutor(LLMExecutor):

    model_name: str = "gpt-3.5-turbo"
    no_system_prompt: bool = False
    openai_base_url: str = "https://api.openai.com/v1"
    context_window: int = 0
    system_prompt: str = None
    generation_config: dict = {
        "temperature": 0.5
    }

    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        model_group = parser.add_argument_group('Model Options')
        model_group.add_argument('--api_key', default=None, help='The API key to access the service')
        model_group.add_argument('--base_url', default=self.openai_base_url, help='Alter the base URL to use third-party service.')
        model_group.add_argument('--model', default=self.model_name, help='Model name. See https://platform.openai.com/docs/models/overview')
        model_group.add_argument('--context_window', default=None, help='Override the context window.')
        model_group.add_argument('--system_prompt', default=self.system_prompt, help='The system prompt that is prepend to the chat history.')
        model_group.add_argument('--no_system_prompt', default=False, action='store_true', help='Disable the system prompt if the model doesn\'t support it.')

        gen_group = parser.add_argument_group('Generation Options', 'Generation options for OpenAI API. See https://github.com/openai/openai-python/blob/main/src/openai/types/chat/completion_create_params.py')
        gen_group.add_argument('-c', '--generation_config', default=None, help='The generation configuration in YAML or JSON format. This can be overridden by other command-line arguments.')
        self.generation_config = expose_function_parameter(
            function=AsyncCompletions.create,
            parser=gen_group,
            defaults=self.generation_config,
            desc_parser=ChatGptDescParser()
        )

    def setup(self):
        self.model_name = self.args.model
        self.openai_base_url = self.args.base_url
        self.system_prompt = self.args.system_prompt if not self.args.no_system_prompt else None
        if not self.LLM_name:
            self.LLM_name = "chatgpt"
        
        context_window = [self.args.context_window] if self.args.context_window is not None else \
                         [v for k, v in CONTEXT_WINDOW.items() if self.model_name in k]
        if len(context_window) == 0:
            logging.warning(f"The context window length of model {self.model_name} not found. Set to minimal value.")
            self.context_window = min(CONTEXT_WINDOW.values())
        else:
            self.context_window = context_window[0]
        logger.debug(f"Context window: {self.context_window}")

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
            
            # Parse and process modelfile
            parsed = Modelfile.from_json(data.get("modelfile", "[]"))
            override_system_prompt, messages = parsed.override_system_prompt, parsed.messages
            system_prompt = override_system_prompt or self.system_prompt

            # Apply parsed modelfile data to Inference
            raw_inputs = messages + json.loads(data.get("input"))
            msg = to_openai_chat_format(raw_inputs)
            if system_prompt is not None:
                msg = [{"content": system_prompt, "role": "system"}] + msg

            msg[-1]['content'] = parsed.before_prompt + msg[-1]['content'] + parsed.after_prompt
            if not msg or len(msg) == 0:
                yield "[No input message entered]"
                return
            
            if not openai_token or len(openai_token) == 0:
                yield "[Please enter your OpenAI API Token in the user settings on the website in order to use the model.]"
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
            openai.base_url = self.openai_base_url
            client = openai.AsyncOpenAI(
                api_key=openai_token,
                base_url=self.openai_base_url
            )
            self.proc = True
            logger.debug(f"msg: {msg}")
            response = await client.chat.completions.create(
                model=self.model_name,
                messages=msg,
                stream=True,
                **self.generation_config
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
                yield "[Invalid OpenAI API Token, please check if the OpenAI API Token is correct.]"
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
    executor = ChatGptExecutor()
    executor.run()