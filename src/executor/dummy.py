import argparse
import os
import sys
import logging
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from base import LLMWorker

logger = logging.getLogger(__name__)

class DummyWorker(LLMWorker):
    def __init__(self):
        super().__init__()

    def _setup(self):
        super()._setup()

        if not self.LLM_name:
            self.LLM_name = "debug"

        self.proc = False

    def llm_compute(self, data):
        for i in """你好我是個語言模型很高興認識你...之類的xD
<<<WARNING>>>
這是一個測試警告
這是二個測試警告
<<</WARNING>>>
中途可以輸出警告
<<<WARNING>>>
警告2，嗨
<<</WARNING>>>
輸出文字模擬結束""":
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
    worker = DummyWorker()
    worker.run()