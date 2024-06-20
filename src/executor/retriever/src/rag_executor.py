import os
import sys
import asyncio
import logging
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from kuwa.executor import LLMExecutor, Modelfile
from .retriever_executor import RetrieverExecutor
from .generator_executor import GeneratorExecutor

logger = logging.getLogger(__name__)

class RAGExecutor(LLMExecutor):
    def __init__(self):
        super().__init__()
        self.retriever = RetrieverExecutor()
        self.generator = GeneratorExecutor()

    def extend_arguments(self, parser):
        self.retriever.extend_arguments(parser=parser)
        self.generator.extend_arguments(parser=parser)

    def setup(self):
        self.retriever.setup()
        self.generator.setup()

    async def llm_compute(self, history: list[dict], modelfile:Modelfile):
        try:
            self.stop = False
            for i in lorem: 
                yield i
                if self.stop:
                    self.stop = False
                    break
                await asyncio.sleep(modelfile.parameters.get("llm_delay", self.args.delay))
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
    executor = RAGExecutor()
    executor.run()