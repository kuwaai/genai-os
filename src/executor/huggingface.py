import os
import sys
import torch
import logging
import time
import re
import json
import pprint
import argparse
from inspect import cleandoc
from typing import Optional
from threading import Thread
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from transformers import AutoTokenizer, GenerationConfig, TextIteratorStreamer, StoppingCriteria, StoppingCriteriaList, AutoModelForCausalLM

from kuwa.executor import LLMExecutor, Modelfile
from kuwa.executor.llm_executor import rectify_chat_history
from kuwa.executor.util import (
    expose_function_parameter,
    read_config,
    merge_config,
)

logger = logging.getLogger(__name__)

class CustomStoppingCriteria(StoppingCriteria):
    def __init__(self):
        self.proc = None

    def __call__(self, input_ids, score, **kwargs) -> bool:
        return not self.proc

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

class HuggingfaceExecutor(LLMExecutor):

    model_path: Optional[str] = None
    limit: int = 1024*3
    stop_words: list = []
    system_prompt: str = "你是一個來自台灣的AI助理，你的名字是 TAIDE，樂於以台灣人的立場幫助使用者，會用繁體中文回答問題。"
    no_system_prompt: bool = False
    timeout: float = 10.0
    generation_config: dict = {
        "max_new_tokens": 4096,
        "do_sample": False,
        "repetition_penalty": 1.0
    }

    # Internal variable
    buffer_length: int = 1 # The length of the sliding window buffer
    
    def __init__(self):
        super().__init__()
    
    def extend_arguments(self, parser):
        model_group = parser.add_argument_group('Model Options')
        model_group.add_argument('--model_path', default=self.model_path, help='Model path. It can be the path to local model or the model name on HuggingFace Hub')
        model_group.add_argument('--visible_gpu', default=None, help='Specify the GPU IDs that this executor can use. Separate by comma.')
        model_group.add_argument('--system_prompt', default=self.system_prompt, help='The system prompt that is prepend to the chat history.')
        model_group.add_argument('--no_system_prompt', default=False, action='store_true', help='Disable the system prompt if the model doesn\'t support it.')
        model_group.add_argument('--limit', type=int, default=self.limit, help='The limit of the user prompt')
        model_group.add_argument('--override_chat_template', default=None,
            help='Override the default chat template provided by the model. Reference: https://huggingface.co/docs/transformers/main/en/chat_templating')
        model_group.add_argument('--stop', default=[], nargs='*', help="Additional end-of-string keywords to stop generation.")
        model_group.add_argument('--timeout', type=float, default=self.timeout, help='The generation timeout in seconds.')
        model_group.add_argument('--load_8bits', action="store_true", default=False, help='Load the model in 8bit.')
        model_group.add_argument('--trust_remote_code', action="store_true", default=False, help='Trust the remote code when loading model.')
        model_group.add_argument('--tokenizer', type=str, default=None, help='Override the tokenizer.')
        
        # Generation Options
        gen_group = parser.add_argument_group('Generation Options', 'GenerationConfig for Transformers. See https://huggingface.co/docs/transformers/en/main_classes/text_generation#transformers.GenerationConfig')
        gen_group.add_argument('-c', '--generation_config', default=None, help='The generation configuration in YAML or JSON format. This can be overridden by other command-line arguments.')
        gen_group.add_argument('--generation_kwargs', default={}, type=str, nargs='*', action=KwargsParser, help='Additional kwargs passed to the HF generate function.')

    def setup(self):
        if self.args.visible_gpu:
            os.environ["CUDA_VISIBLE_DEVICES"] = self.args.visible_gpu

        self.model_path = self.args.model_path
        self.tokenizer_name = self.args.tokenizer if self.args.tokenizer is not None else self.model_path
        if not self.model_path:
            raise Exception("You need to configure a local or huggingface model path!")

        self.load_8bits = self.args.load_8bits
        self.trust_remote_code = self.args.trust_remote_code
        model_dtype = {}
        if self.load_8bits:
            model_dtype["load_in_8bit"] = True
        else:
            model_dtype["torch_dtype"] = torch.float16 

        self.model = AutoModelForCausalLM.from_pretrained(
            self.model_path,
            device_map="auto",
            trust_remote_code=self.trust_remote_code,
            **model_dtype
        )
        self.tokenizer = AutoTokenizer.from_pretrained(
            self.tokenizer_name,
            trust_remote_code=self.trust_remote_code,
        )
        self.system_prompt = self.args.system_prompt
        self.no_system_prompt = self.args.no_system_prompt
        self.timeout = self.args.timeout
        self.stop_words = [i for i in set([self.tokenizer.eos_token, self.tokenizer.bos_token] + self.args.stop) if i != None]
        self.buffer_length = max([len(k) for k in self.stop_words] or [1])
        self.tokenizer.chat_template = self.args.override_chat_template or \
                                       self.tokenizer.chat_template or \
                                       self.tokenizer.default_chat_template
        self.CSC = CustomStoppingCriteria()

        # Setup generation config
        self.generation_config["pad_token_id"] = self.tokenizer.eos_token_id
        default_gconf = GenerationConfig().to_dict()
        file_gconf = read_config(self.args.generation_config) if self.args.generation_config else {}
        self.generation_config = merge_config(base=default_gconf, top=self.generation_config)
        self.generation_config = merge_config(base=self.generation_config, top=file_gconf)
        self.generation_config = merge_config(base=self.generation_config, top=self.args.generation_kwargs)

        logger.debug(f"Stop words: {self.stop_words}")
        logger.debug(f"Buffer length: {self.buffer_length}")
        logger.debug(f"Chat template: {self.tokenizer.chat_template}")
        logger.debug(f"Generation config:\n{pprint.pformat(self.generation_config, indent=2)}")

    def synthesis_prompt(self, history: list, system_prompt: str, template: str = None):
        """
        Synthesis the prompt from chat history.
        """
        history = history.copy()
        if not self.no_system_prompt and system_prompt:
            history.insert(0, {"role": "system", "content": system_prompt})

        chat_template_backup = self.tokenizer.chat_template
        self.tokenizer.chat_template = template or self.tokenizer.chat_template
        prompt = None
        try:
            prompt = self.tokenizer.apply_chat_template(
                history, tokenize=True, add_generation_prompt=True, return_tensors='pt'
            )
        except Exception as e:
            logger.exception(f"Error in template `{self.tokenizer.chat_template}` with error: `{e}`")
        finally:
            self.tokenizer.chat_template = chat_template_backup

        return prompt

    async def llm_compute(self, history: list[dict], modelfile:Modelfile):

        # Apply modelfile
        system_prompt = modelfile.override_system_prompt or self.system_prompt
        prepended_messages = rectify_chat_history(modelfile.messages)
        if len(history) > 0 and history[-1]['role'] == "user":
            history[-1]['content'] = "{before_prompt}{original_prompt}{after_prompt}".format(
                before_prompt = modelfile.before_prompt,
                original_prompt = history[-1]['content'],
                after_prompt = modelfile.after_prompt
            )

        # Trim the history to fit into the context window
        prompt_embedding = []
        while True:
            prompt_embedding = self.synthesis_prompt(prepended_messages + history, system_prompt, modelfile.template)
            if prompt_embedding.shape[1] <= self.limit: break

            history = rectify_chat_history(history[1:])
            if len(history) == 0:
                logging.debug("Aborted since the input message exceeds the limit.")
                yield "[Sorry, The input message is too long!]"
                return

        logging.debug(f"Prompt: {self.tokenizer.decode(prompt_embedding[0])}")
        prompt_embedding = prompt_embedding.to(self.model.device)
        streamer = TextIteratorStreamer(self.tokenizer, skip_prompt=True, timeout=self.timeout)
        thread = Thread(target=self.model.generate, kwargs=dict(
            input_ids=prompt_embedding,
            streamer=streamer,
            generation_config=GenerationConfig(
                **merge_config(self.generation_config, modelfile.parameters["llm_"])
            ),
            stopping_criteria=StoppingCriteriaList([self.CSC])
        ), daemon=True)
        
        try:
            thread.start()
            self.CSC.proc = thread

            buffer = ""
            for chunk in streamer:
                buffer += chunk
                for word in self.stop_words:
                    if word not in buffer: continue
                    logger.debug(f"{word} founded!")
                    buffer = buffer.split(word)[0]
                    self.CSC.proc = None
                    break

                if not self.CSC.proc: break
                
                if len(buffer) > self.buffer_length:
                    output_length = len(buffer) - self.buffer_length
                    if self.in_debug(): print(end=buffer[:output_length], flush=True)
                    yield buffer[:output_length]
                    buffer = buffer[output_length:]
            
            if len(buffer) > 0:
                if self.in_debug(): print(end=buffer, flush=True)
                yield buffer # Flush buffer

        except Exception as e:
            logger.exception("Error occurs during generation.")
            yield "[Oops, Cuda out of memory! Please try toggle off chained state, or remove some texts.]"
        finally:
            self.CSC.proc = None
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
    executor = HuggingfaceExecutor()
    executor.run()