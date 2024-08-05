import os
import sys
import asyncio
import logging
import json
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from kuwa.executor import LLMExecutor, Modelfile
from kuwa.executor.util import merge_config
from kuwa.client import KuwaClient

logger = logging.getLogger(__name__)

class GeneratorExecutor(LLMExecutor):
    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        """
        Override this method to add custom command-line arguments.
        """
        generator_group = parser.add_argument_group('Generator Options')
        generator_group.add_argument('--kuwa_api_url', default="http://127.0.0.1/", help='The API base URL of Kuwa multi-chat WebUI')
        generator_group.add_argument('--model', default=None, help='The model name (access code) on Kuwa multi-chat WebUI')
        generator_group.add_argument('--limit', default=3072, type=int, help='The limit of the LLM\'s context window')
        generator_group.add_argument('--with_ref', action="store_true", help='Append the reference at the end')

    def setup(self):
        self.kuwa_api_url = self.args.kuwa_api_url
        self.generator_param = {
            "kuwa_api_url": self.args.kuwa_api_url,
            "model": self.args.model,
            "limit": self.args.limit,
        }
        self.with_ref = self.args.with_ref
        self.stop = False

    def squash_history(self, history: list[dict], modelfile:Modelfile):
        result = ""
        if modelfile.override_system_prompt:
            result += modelfile.override_system_prompt + "\n"
        result += "\n\n".join([i["content"] for i in history])
        return result

    async def llm_compute(self, history: list[dict], modelfile:Modelfile):
        kuwa_api_token = modelfile.parameters["_"]["user_token"]
        generator_param = merge_config(self.generator_param, modelfile.parameters["llm_"])
        try:
            llm = KuwaClient(
                base_url = self.kuwa_api_url,
                kernel_base_url = self.kernel_url,
                model=generator_param["model"],
            )
            
            # Trim the history
            modified_history = history.copy()
            llm_input = ""
            while True:
                llm_input = self.squash_history(modified_history)
                modified_history = modified_history[1:]

                if len(llm_input) <= generator_param["limit"]: break

            self.logger.info('LLM input: {}'.format(llm_input))
            generator = self.llm.chat_complete(
                auth_token=kuwa_api_token,
                messages=[{"role": "user", "content": llm_input}]
            )

            self.stop = False
            async for chunk in generator:
                if self.stop:
                    self.stop = False
                    break
                yield chunk

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
    executor = GeneratorExecutor()
    executor.run()