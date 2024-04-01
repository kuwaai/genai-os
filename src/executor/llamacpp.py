import os
import sys
import logging
import time
import json
from typing import Optional
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from llama_cpp import Llama
import llama_cpp.llama_cpp as llama_cpp
import llama_cpp.llama_chat_format as llama_chat_format

from kuwa.executor import LLMWorker

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
class LlamaCppWorker(LLMWorker):

    model_path: Optional[str] = None
    limit: int = 1024*3
    context_window: int = 4096
    stop_words: list = []
    system_prompt: str = "你是一個來自台灣的AI助理，你的名字是 TAIDE，樂於以台灣人的立場幫助使用者，會用繁體中文回答問題。"
    temperature: float = 0.5
    
    def __init__(self):
        super().__init__()

    def _create_parser(self):
        parser = super()._create_parser()
        parser.add_argument('--model_path', default=self.model_path, help='Model path')
        parser.add_argument('--gpu_config', default=None, help='GPU config')
        parser.add_argument('--ngl', type=int, default=0, help='Number of layers to offload to GPU. If -1, all layers are offloaded')

        parser.add_argument('--limit', type=int, default=self.limit, help='The limit of the context window')
        parser.add_argument('--system_prompt', default=self.system_prompt, help='System prompt. Disable it by setting it to an empty string if the model doesn\'t support')
        parser.add_argument('--context_window', default=self.context_window, help='The context window of the model')
        parser.add_argument('--stop', default=[], nargs='*', help="Additional end-of-string keywords to stop generation.")
        parser.add_argument('--override_chat_template', default=None,
            help='Override the default chat template provided by the model. Reference: https://huggingface.co/docs/transformers/main/en/chat_templating')

        parser.add_argument('-t', '--temperature', type=float, default=self.temperature, help=' The temperature to use for sampling. Setting it to a negative value enables greedy decoding.')
        return parser

    def _setup(self):
        super()._setup()

        if self.args.gpu_config:
            os.environ["CUDA_VISIBLE_DEVICES"] = self.args.gpu_config

        self.model_path = self.args.model_path
        if not self.model_path:
            raise Exception("You need to configure a .gguf model path!")

        if not self.LLM_name:
            self.LLM_name = "gguf"

        self.limit = self.args.limit
        self.system_prompt = self.args.system_prompt
        self.context_window = self.args.context_window
        self.temperature = self.args.temperature
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
        
        logger.debug(f"Chat handler:{self.model.chat_handler}")
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
                max_tokens=None,
                temperature=self.temperature,
                echo=False,
                stream=True
            )
            self.serving_generator = output_generator
            
            for i in output_generator:
                logging.debug(i)
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