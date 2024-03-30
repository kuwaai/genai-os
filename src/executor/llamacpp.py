import os
import sys
import logging
import time
from typing import Optional
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from llama_cpp import Llama

from kuwa.executor import LLMWorker

logger = logging.getLogger(__name__)

class LlamaCppWorker(LLMWorker):

    model_path: Optional[str] = None
    limit: int = 1024*3
    
    def __init__(self):
        super().__init__()

    def _create_parser(self):
        parser = super()._create_parser()
        parser.add_argument('--limit', type=int, default=self.limit, help='The limit of the context window')
        parser.add_argument('--model_path', default=self.model_path, help='Model path')
        parser.add_argument('--gpu_config', default=None, help='GPU config')
        parser.add_argument('--ngl', type=int, default=0, help='Number of layers to offload to GPU. If -1, all layers are offloaded')
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

        self.model = Llama(model_path=self.model_path, n_gpu_layers=self.args.ngl)
        self.proc = False

    async def llm_compute(self, data):
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
                    if self.in_debug(): print(end=i["choices"][0]["text"], flush=True)
                    yield i["choices"][0]["text"]
            else:
                yield "[Sorry, The input message is too long!]"

        except Exception as e:
            logger.error("Error occurs while processing request.")
            raise e
        finally:
            logger.debug("finished")

if __name__ == "__main__":
    worker = LlamaCppWorker()
    worker.run()