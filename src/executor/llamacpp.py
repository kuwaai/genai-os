import re
import os
import sys
import logging
import time
import json
import pprint
from typing import Optional
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from llama_cpp import Llama
import llama_cpp.llama_cpp as llama_cpp
import llama_cpp.llama_chat_format as llama_chat_format

from kuwa.executor import LLMWorker
from kuwa.executor.util import expose_function_parameter, read_config, merge_config, DescriptionParser

logger = logging.getLogger(__name__)

class ReflectiveLlama(Llama):
    """
    A fake Llama class the reflect the prompt.
    It can be used in inspecting the formatted prompt.
    """
    def __init__(self, *args, **kwargs):
        self.verbose = False
    def create_completion(self, prompt, *args, **kwargs):
        t = int(time.time())
        return {
            "id": f"cmpl-reflect-{t}",
            "object": "text_completion",
            "created": t,
            "model": "reflective-llama",
            "choices": [
                {
                "text": prompt,
                "index": 0,
                "logprobs": None,
                "finish_reason": "stop"
                }
            ],
            "usage": {
                "prompt_tokens": 0,
                "completion_tokens": 0,
                "total_tokens": 0
            }
        }

class LlamaCppDescParser(DescriptionParser):
    """
    Extract parameter description from Llama.create_completion.
    Ref: https://github.com/abetlen/llama-cpp-python/blob/f96de6d92087243f6430449c7a082f8a9071185a/llama_cpp/llama.py#L1423
    """
    def __call__(self, doc:str, name:str) -> str:
        match = re.search(rf"{name}:\s+(.*)\n", doc)
        if match:
            description = match.group(1)
        else:
            description = None
        return description

class LlamaCppWorker(LLMWorker):

    model_path: Optional[str] = None
    limit: int = 1024*3
    context_window: int = 4096
    stop_words: list = []
    system_prompt: str = "你是一個來自台灣的AI助理，你的名字是 TAIDE，樂於以台灣人的立場幫助使用者，會用繁體中文回答問題。"
    generation_config: dict = {
        "max_tokens": None
    }
    
    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        model_group = parser.add_argument_group('Model Options')
        model_group.add_argument('--model_path', default=self.model_path, help='Model path')
        model_group.add_argument('--visible_gpu', default=None, help='Specify the GPU IDs that this worker can use. Separate by comma.')
        model_group.add_argument('--ngl', type=int, default=0, help='Number of layers to offload to GPU. If -1, all layers are offloaded')

        model_group.add_argument('--limit', type=int, default=self.limit, help='The limit of the input tokens')
        model_group.add_argument('--system_prompt', default=self.system_prompt, help='System prompt. Disable it by setting it to an empty string if the model doesn\'t support')
        model_group.add_argument('--context_window', default=self.context_window, help='The context window of the model')
        model_group.add_argument('--stop', default=[], nargs='*', help="Additional end-of-string keywords to stop generation.")
        model_group.add_argument('--override_chat_template', default=None,
            help='Override the default chat template provided by the model. See https://huggingface.co/docs/transformers/main/en/chat_templating')

        # Generation Options
        gen_group = parser.add_argument_group('Generation Options', 'Generation options for llama.cpp. See https://llama-cpp-python.readthedocs.io/en/latest/api-reference/#llama_cpp.Llama.create_completion')
        gen_group.add_argument('-c', '--generation_config', default=None, help='The generation configuration in YAML or JSON format. This can be overridden by other command-line arguments.')
        self.generation_config = expose_function_parameter(
            function=Llama.create_completion,
            parser=gen_group,
            defaults=self.generation_config,
            desc_parser=LlamaCppDescParser()
        )

    def setup(self):
        if self.args.visible_gpu:
            os.environ["CUDA_VISIBLE_DEVICES"] = self.args.visible_gpu

        self.model_path = self.args.model_path
        if not self.model_path:
            raise Exception("You need to configure a .gguf model path!")

        if not self.LLM_name:
            self.LLM_name = "gguf"

        self.limit = self.args.limit
        self.system_prompt = self.args.system_prompt
        self.context_window = self.args.context_window
        self.model = Llama(model_path=self.model_path, n_gpu_layers=self.args.ngl, n_ctx=self.context_window)

        # Get EOS and BOS token.
        # Reference: https://github.com/abetlen/llama-cpp-python/blob/aa9f1ae011fbc22893750209af500fee3167f21c/llama_cpp/llama.py#L403
        eos_token_id = int(self.model.metadata.get("tokenizer.ggml.eos_token_id", self.model.token_eos()))
        bos_token_id = int(self.model.metadata.get("tokenizer.ggml.bos_token_id", self.model.token_bos()))
        eos_token = self.model._model.token_get_text(eos_token_id)
        bos_token = self.model._model.token_get_text(bos_token_id)
        
        self.stop_words = list(set([eos_token] + self.args.stop))
        
        # Setup the handler
        chat_handler = self.model.chat_handler or llama_chat_format.get_chat_completion_handler(
            self.model.chat_format
        )
        if self.args.override_chat_template:
            chat_handler = llama_chat_format.Jinja2ChatFormatter(
                    template=self.args.override_chat_template, eos_token=eos_token, bos_token=bos_token
                ).to_chat_handler()
        self.model.chat_handler = chat_handler
        
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
        logger.debug(f"Stop words: {self.stop_words}")

        self.serving_generator = None

    def synthesis_prompt(self, history: list):
        """
        Synthesis the prompt from chat history.
        """
        history = history.copy()
        if self.system_prompt:
            history.insert(0, {"role": "system", "content": self.system_prompt})

        prompt = self.model.chat_handler(
            llama = ReflectiveLlama(),
            messages = history
        )["choices"][0]["message"]["content"]
        return prompt

    def rectify_history(self, history: list):
        """
        Ensure the history begin with "user."
        """
        first_user_idx = 0
        while history[first_user_idx]["role"] != "user" and first_user_idx+1 < len(history)-1:
            first_user_idx += 1
        history = history[first_user_idx:]
        return history

    async def llm_compute(self, data):
        history = json.loads(data.get("input"))
        history = [
            {
                "role": "assistant" if record["isbot"] else "user",
                "content": record["msg"]
            }
            for record in history
        ]
        history = self.rectify_history(history)

        try:
            # Trim the history to fit into the context window
            prompt = ""
            while True:
                prompt = self.synthesis_prompt(history)
                prompt_length = len(self.model.tokenize(
                    text=prompt.encode('UTF-8', 'ignore'),
                    add_bos=False, special=False
                ))
                logging.debug(f"Prompt ({prompt_length} tokens): {prompt}")
                if prompt_length <= self.limit: break

                history = self.rectify_history(history[1:])
                if len(history) == 0:
                    logging.debug("Aborted since the input message exceeds the limit.")
                    yield "[Sorry, The input message is too long!]"
                    return
            
            output_generator = self.model.create_completion(
                prompt,
                stop=self.stop_words,
                echo=False,
                stream=True,
                **self.generation_config
            )
            self.serving_generator = output_generator
            
            for i in output_generator:
                chunk = i["choices"][0]["text"]
                if self.in_debug(): print(end=chunk, flush=True)
                yield chunk

        except Exception as e:
            logger.error("Error occurs while processing request.")
            raise e
        finally:
            logger.debug("finished")
    
    async def abort(self):
        if not self.serving_generator:
            return "There's not running generation request to abort."
        self.serving_generator.close()
        self.serving_generator = None
        logger.debug("aborted")
        return "Aborted"

if __name__ == "__main__":
    worker = LlamaCppWorker()
    worker.run()