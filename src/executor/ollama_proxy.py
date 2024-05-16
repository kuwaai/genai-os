import re
import os
import sys
import logging
import json
import typing
import pprint
import argparse
from textwrap import dedent
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
import ollama

from kuwa.executor import LLMExecutor
from kuwa.executor.util import expose_function_parameter, read_config, merge_config, DescriptionParser

logger = logging.getLogger(__name__)

class KwargsParser(argparse.Action):
    """Parser action class to parse kwargs of form key=value"""
    def __call__(self, parser, namespace, values, option_string=None):
        setattr(namespace, self.dest, dict())
        for val in values:
            if '=' not in val:
                raise ValueError(
                    (
                        'Argument parsing error, kwargs are expected in'
                        ' the form of key=value.'
                    )
                )
            kwarg_k, kwarg_v = val.split('=')
            try:
                converted_v = int(kwarg_v)
            except ValueError:
                try:
                    converted_v = float(kwarg_v)
                except ValueError:
                    converted_v = kwarg_v
            getattr(namespace, self.dest)[kwarg_k] = converted_v

class OllamaExecutor(LLMExecutor):

    ollama_host: str = None
    model_name: str = "llama3"
    limit: int = 1024*7
    context_window: int = 8192
    system_prompt: str = None
    ollama_options: dict = {}

    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        model_group = parser.add_argument_group('Model Options')
        model_group.add_argument('--ollama_host', default=self.ollama_host, help='The host of the Ollama server. e.g. http://127.0.0.1:11434')
        model_group.add_argument('--model', default=self.model_name, help='Model name. See https://ollama.com/library')
        model_group.add_argument('--context_window', type=int, default=self.context_window, help='The context window of the model')
        model_group.add_argument('--limit', type=int, default=self.limit, help='The limit of the user prompt')
        model_group.add_argument('--system_prompt', default=self.system_prompt, help='The system prompt that is prepend to the chat history.')

        gen_group = parser.add_argument_group('Generation Options', 'Generation options for Ollama API. See https://github.com/ollama/ollama/blob/main/docs/api.md')
        gen_group.add_argument('-c', '--generation_config', default=None, help='The generation configuration in YAML or JSON format. This can be overridden by other command-line arguments.')
        gen_group.add_argument('--generation_kwargs', default={}, type=str, nargs='*', action=KwargsParser, help='Additional model parameters listed in the documentation for the Modelfile such as `temperature=0.5`')

    def setup(self):
        self.ollama_host = self.args.ollama_host
        self.model_name = self.args.model
        self.context_window = self.args.context_window
        self.limit = self.args.limit
        self.system_prompt = self.args.system_prompt
        if self.LLM_name is None: self.LLM_name = "ollama"
        
        # Setup generation config
        file_gconf = read_config(self.args.generation_config) if self.args.generation_config else {}
        arg_gconf = self.args.generation_kwargs
        self.ollama_options = merge_config(base=self.ollama_options, top=file_gconf)
        self.ollama_options = merge_config(base=self.ollama_options, top=arg_gconf)

        logger.debug(f"Ollama options:\n{pprint.pformat(self.ollama_options, indent=2)}")

        self.client = ollama.AsyncClient(
            host = self.ollama_host
        )

        self.proc = False

    async def llm_compute(self, data):
        try:
            # Parse and process modelfile
            override_system_prompt, messages, _ = self.parse_modelfile(data.get("modelfile", "[]"))
            system_prompt = override_system_prompt or self.system_prompt

            # Apply parsed modelfile data to inference
            raw_inputs = messages + json.loads(data.get("input"))
            msg = [{"content":i['msg'], "role":"assistant" if i['isbot'] else "user"} for i in raw_inputs]
            if system_prompt is not None:
                msg = [{"content": system_prompt, "role": "system"}] + msg

            if not msg or len(msg) == 0:
                yield "[No input message entered]"
                return

            # [TODO] Trim the history to fit into the context window

            self.proc = True
            response = await self.client.chat(
                model=self.model_name,
                messages=msg,
                options=self.ollama_options,
                stream=True
            )
            async for i in response:
                if i['done']: continue

                chunk = i['message']['content']
                if not self.proc: break
                if not chunk: continue

                if self.in_debug(): print(end=chunk, flush=True)
                yield chunk

        except Exception as e:
            logger.exception("Error occurs when calling Ollama API")
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
    executor = OllamaExecutor()
    executor.run()