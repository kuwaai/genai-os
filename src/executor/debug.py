import argparse
import os
import sys
import logging
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from base import LLMWorker

logger = logging.getLogger(__name__)

class DebugWorker(LLMWorker):
    def __init__(self):
        super().__init__()

    def _setup(self):
        super()._setup()

        if not self.LLM_name:
            self.LLM_name = "debug"

        self.proc = False

    def llm_compute(self, data):
        for i in "".join([i['msg'] for i in eval(data.get("input").replace("true","True").replace("false","False"))]).strip():
            yield i
            time.sleep(0.02)
            if not self.proc: break
        self.proc = False
        self.Ready = True
        logger.debug("finished")

    def abort(self):
        if self.proc:
            self.proc = False
            logger.debug("aborted")
            return "Aborted"
        return "No process to abort"

if __name__ == "__main__":
    worker = DebugWorker()
    worker.run()