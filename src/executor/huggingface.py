import os
import sys
import torch
import logging
import time
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from transformers import AutoTokenizer, GenerationConfig, TextIteratorStreamer, StoppingCriteria, StoppingCriteriaList, AutoModelForCausalLM
from threading import Thread

from framework import LLMWorker

logger = logging.getLogger(__name__)

class CustomStoppingCriteria(StoppingCriteria):
    def __init__(self):
        pass

    def __call__(self, input_ids, score, **kwargs) -> bool:
        return not self.proc

class HuggingfaceWorker(LLMWorker):
    def __init__(self):
        super().__init__()

    def _setup(self):
        super()._setup()

        if not self.model_path:
            raise Exception("You need to configure a local or huggingface model path!")

        if not self.LLM_name:
            self.LLM_name = "huggingface"
                
        self.model = AutoModelForCausalLM.from_pretrained(self.model_path,device_map="auto", torch_dtype=torch.float16)
        self.tokenizer = AutoTokenizer.from_pretrained(self.model_path)
        self.CSC = CustomStoppingCriteria()
        self.CSC.proc = False

    async def llm_compute(self, data):
        prompts = "<s>[INST] {0} [/INST]{1}"
        stopwords = ["</s>","<s>"]
        bufferLength = max([len(k) for k in stopwords]) if stopwords else -1
        try:
            s = time.time()
            history = [i['msg'] for i in eval(data.get("input").replace("true","True").replace("false","False"))]
            while len("".join(history)) > self.limit:
                del history[0]
                if history: del history[0]
            if len(history) != 0:
                history[0] = "<<SYS>>\n你是一個來自台灣的AI助理，你的名字是 TAIDE，樂於以台灣人的立場幫助使用者，會用繁體中文回答問題。\n<</SYS>>\n\n" + history[0]
                history.append("")
                history = [prompts.format(history[i], ("{0}" if i+1 == len(history) - 1 else " {0} </s>").format(history[i + 1])) for i in range(0, len(history), 2)]
                history = "".join(history)
                encoding = self.tokenizer(history, return_tensors='pt', add_special_tokens=False).to(self.model.device)
                
                streamer = TextIteratorStreamer(self.tokenizer,skip_prompt=True,timeout=2)
                l = encoding['input_ids'].size(1)
                
                thread = Thread(target=self.model.generate, kwargs=dict(input_ids=encoding['input_ids'], streamer=streamer,
                    generation_config=GenerationConfig(
                        max_new_tokens=4096,
                        pad_token_id=self.tokenizer.eos_token_id,
                        do_sample=False,
                        repetition_penalty = 1.0
                    ),stopping_criteria=StoppingCriteriaList([self.CSC])),daemon=True)
                thread.start()
                self.CSC.proc = thread
                buffer = ""
                if bufferLength: logger.debug(f"buffering with {bufferLength} length")
                for i in streamer:
                    torch.cuda.empty_cache()
                    if bufferLength != -1:
                        buffer += i
                        for o in stopwords:
                            if o in buffer: 
                                self.CSC.proc = None
                                logger.debug(f"{o} founded!")
                                buffer = buffer.split(o)[0]
                                break
                        while len(buffer) > bufferLength:
                            if self.in_debug(): print(end=buffer[0], flush=True)
                            yield buffer[0]
                            buffer = buffer[1:]
                        if not self.CSC.proc:
                            if self.in_debug(): print(end=buffer, flush=True)
                            yield buffer # clear buffer
                    else:
                        if self.in_debug(): print(end=i.replace("</s>",""),flush=True)
                        yield i.replace("</s>","")
                    if not self.CSC.proc: break
                thread.join()
                torch.cuda.empty_cache()
            else:
                yield "[Sorry, The input message is too long!]"
        except Exception as e:
            logger.exception("Error occurs during generation.")
            yield "[Oops, Cuda out of memory! Please try toggle off chained state, or remove some texts.]"
        finally:
            torch.cuda.empty_cache()
            self.Ready = True
            logger.debug("finished")
            
    async def abort(self):
        if self.CSC.proc:
            tmp = self.CSC.proc
            self.CSC.proc = None
            logger.debug("aborting...")
            tmp.join()
            logger.debug("aborted")
            torch.cuda.empty_cache()
            return "Aborted"
        return "No process to abort"

if __name__ == "__main__":
    worker = HuggingfaceWorker()
    worker.run()