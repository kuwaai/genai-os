import os
import sys
import asyncio
import logging
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from kuwa.executor import LLMWorker

logger = logging.getLogger(__name__)

class DebugWorker(LLMWorker):
    def __init__(self):
        super().__init__()

    def _create_parser(self):
        """
        Override this method to add custom command-line arguments.
        Remember to invoke "_create_parser" of the parent class.
        """
        parser = super()._create_parser()
        parser.add_argument('--delay', type=int, default=0.02, help='Inter-token delay')
        return parser

    def _setup(self):
        super()._setup()

        if not self.LLM_name:
            self.LLM_name = "debug"

        self.stop = False

    async def llm_compute(self, data):
        try:
            self.stop = False
            for i in "".join([i['msg'] for i in eval(data.get("input").replace("true","True").replace("false","False"))]).strip():
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
    worker = DebugWorker()
    worker.run()