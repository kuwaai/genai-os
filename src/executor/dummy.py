import os
import sys
import asyncio
import logging
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from kuwa.executor import LLMWorker

logger = logging.getLogger(__name__)

lorem = """你好我是個語言模型很高興認識你...之類的xD
<<<WARNING>>>
這是一個測試警告
這是二個測試警告
<<</WARNING>>>
中途可以輸出警告
<<<WARNING>>>
警告2，嗨
<<</WARNING>>>
輸出文字模擬結束"""

class DummyWorker(LLMWorker):
    def __init__(self):
        super().__init__()

    def _create_parser(self):
        parser = super()._create_parser()
        parser.add_argument('--delay', type=int, default=0.02, help='Inter-token delay')
        return parser

    def _setup(self):
        super()._setup()

        if not self.LLM_name:
            self.LLM_name = "dummy"

        self.stop = False

    async def llm_compute(self, data):
        try:
            self.stop = False
            for i in lorem: 
                yield i
                if self.stop:
                    self.stop = False
                    break
                await asyncio.sleep(self.args.delay)
        except Exception as e:
            logger.exception("Error occurs during generation.")
            yield str(e)
        finally:
            logger.debug("finished")

    async def abort(self):
        self.stop = True
        logger.debug("aborted")
        return "Aborted"

if __name__ == "__main__":
    worker = DummyWorker()
    worker.run()