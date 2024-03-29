import argparse
import os
import sys
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from llama_cpp import Llama
from base import LLMWorker

logger = logging.getLogger(__name__)

class LlamaCppWorker(LLMWorker):
    def __init__(self):
        super().__init__()

    def _setup(self):
        super()._setup()

        if not self.model_path:
            raise Exception("You need to configure a .gguf model path!")

        if not self.LLM_name:
            self.LLM_name = "gguf"

        self.model = Llama(model_path=self.model_path)
        self.proc = False

    def llm_compute(self, data):
        try:
            s = time.time()
            history = [i['msg'] for i in eval(data.get("input").replace("true","True").replace("false","False"))]
            while len("".join(history)) > self.limit:
                del history[0]
                if history: del history[0]
            if len(history) != 0:
                history.append("")
                history = ["<s>[INST] {0} [/INST]{1}".format(history[i], ("{0}" if i+1 == len(history) - 1 else " {0} </s>").format(history[i + 1])) for i in range(0, len(history), 2)]
                history = "".join(history)
                output = self.model.create_completion(
                    history,
                    max_tokens=4096,
                    stop=["</s>"],
                    echo=False,
                    stream=True
                )
                
                for i in output:
                    logger.debug(end=i["choices"][0]["text"],flush=True)
                    yield i["choices"][0]["text"]
            else:
                yield "[Sorry, The input message is too long!]"

        except Exception as e:
            logger.error("Error occurs while processing request.")
            raise e
        finally:
            self.Ready = True
            logger.debug("finished")

if __name__ == "__main__":
    worker = LlamaCppWorker()
    worker.run()