import os
import sys
import torch
import logging
import time
import re
import json
from inspect import cleandoc
from typing import Optional
from threading import Thread
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from transformers import AutoTokenizer, GenerationConfig, TextIteratorStreamer, StoppingCriteria, StoppingCriteriaList, AutoModelForCausalLM

from kuwa.executor import LLMWorker

logger = logging.getLogger(__name__)

class CustomStoppingCriteria(StoppingCriteria):
    def __init__(self):
        self.proc = None

    def __call__(self, input_ids, score, **kwargs) -> bool:
        return not self.proc

class HuggingfaceWorker(LLMWorker):

    model_path: Optional[str] = None
    limit: int = 1024*3
    context_window: int = 4096
    stop_words: list = []
    system_prompt: str = "你是一個來自台灣的AI助理，你的名字是 TAIDE，樂於以台灣人的立場幫助使用者，會用繁體中文回答問題。"

    # Internal variable
    buffer_length: int = 1 # The length of the sliding window buffer
    
    def __init__(self):
        super().__init__()
    
    def _create_parser(self):
        parser = super()._create_parser()
        parser.add_argument('--model_path', default=self.model_path, help='Model path. It can be the path to local model or the model name on HuggingFace Hub')
        parser.add_argument('--gpu_config', default=None, help='GPU config')
        parser.add_argument('--system_prompt', default=self.system_prompt, help='System prompt. Disable it by setting it to an empty string if the model doesn\'t support')
        parser.add_argument('--limit', type=int, default=self.limit, help='The limit of the user prompt')
        parser.add_argument('--context_window', default=self.context_window, help='The context window of the model')
        parser.add_argument('--override_chat_template', default=None,
            help='Override the default chat template provided by the model. Reference: https://huggingface.co/docs/transformers/main/en/chat_templating')
        parser.add_argument('--stop', default=[], nargs='*', help="Additional end-of-string keywords to stop generation.")
        
        return parser

    def _setup(self):
        super()._setup()

        if self.args.gpu_config:
            os.environ["CUDA_VISIBLE_DEVICES"] = self.args.gpu_config

        self.model_path = self.args.model_path
        if not self.model_path:
            raise Exception("You need to configure a local or huggingface model path!")

        if not self.LLM_name:
            self.LLM_name = "huggingface"
                
        self.model = AutoModelForCausalLM.from_pretrained(self.model_path,device_map="auto", torch_dtype=torch.float16)
        self.tokenizer = AutoTokenizer.from_pretrained(self.model_path)
        self.system_prompt = self.args.system_prompt
        self.context_window = self.args.context_window
        self.stop_words = list(set([self.tokenizer.eos_token, self.tokenizer.bos_token] + self.args.stop))
        self.buffer_length = max([len(k) for k in self.stop_words] or [1])
        self.tokenizer.chat_template = self.args.override_chat_template or \
                                       self.tokenizer.chat_template or \
                                       self.tokenizer.default_chat_template
        self.CSC = CustomStoppingCriteria()

        logger.debug(f"Stop words: {self.stop_words}")
        logger.debug(f"Buffer length: {self.buffer_length}")
        logger.debug(f"Chat template: {self.tokenizer.chat_template}")

    def synthesis_prompt(self, history: list):
        """
        Synthesis the prompt from chat history.
        """
        history = history.copy()
        if self.system_prompt:
            history.insert(0, {"role": "system", "content": self.system_prompt})
        prompt = self.tokenizer.apply_chat_template(
            history, tokenize=True, add_generation_prompt=True, return_tensors='pt'
        )
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

        # Trim the history to fit into the context window
        prompt_embedding = []
        while True:
            prompt_embedding = self.synthesis_prompt(history)
            if prompt_embedding.shape[1] <= self.limit: break

            history = self.rectify_history(history[1:])
            if len(history) == 0:
                logging.debug("Aborted since the input message exceeds the limit.")
                yield "[Sorry, The input message is too long!]"
                return

        logging.debug(self.tokenizer.decode(prompt_embedding[0]))
        prompt_embedding = prompt_embedding.to(self.model.device)
        streamer = TextIteratorStreamer(self.tokenizer, skip_prompt=True, timeout=2)
        thread = Thread(target=self.model.generate, kwargs=dict(
            input_ids=prompt_embedding,
            streamer=streamer,
            generation_config=GenerationConfig(
                max_new_tokens=self.context_window,
                pad_token_id=self.tokenizer.eos_token_id,
                do_sample=False,
                repetition_penalty = 1.0
            ),
            stopping_criteria=StoppingCriteriaList([self.CSC])
        ), daemon=True)
        
        try:
            thread.start()
            self.CSC.proc = thread

            buffer = ""
            for chunk in streamer:
                torch.cuda.empty_cache()
                buffer += chunk
                for word in self.stop_words:
                    if word not in buffer: continue
                    self.CSC.proc = None
                    logger.debug(f"{word} founded!")
                    buffer = buffer.split(word)[0]
                    break
                if not self.CSC.proc:
                    if self.in_debug(): print(end=buffer, flush=True)
                    yield buffer # Flush buffer
                    break
                elif len(buffer) > self.buffer_length:
                    output_length = len(buffer) - self.buffer_length
                    if self.in_debug(): print(end=buffer[:output_length], flush=True)
                    yield buffer[:output_length]
                    buffer = buffer[output_length:]
                
        except Exception as e:
            logger.exception("Error occurs during generation.")
            yield "[Oops, Cuda out of memory! Please try toggle off chained state, or remove some texts.]"

        finally:
            thread.join()
            torch.cuda.empty_cache()
            logger.debug("finished")
            
    async def abort(self):
        if not self.CSC.proc: return "No process to abort"

        thread = self.CSC.proc
        self.CSC.proc = None
        logger.debug("aborting...")
        thread.join()
        logger.debug("aborted")
        torch.cuda.empty_cache()
        return "Aborted"

if __name__ == "__main__":
    worker = HuggingfaceWorker()
    worker.run()