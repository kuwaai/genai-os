import os
import sys
import asyncio
import logging
import json
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from kuwa.executor import LLMExecutor, Modelfile
from kuwa.executor.util import merge_config

logger = logging.getLogger(__name__)

class KuwaLlmClient:

    def __init__(self, base_url="http://localhost", kernel_base_url="http://localhost:9000", model=None, auth_token=None, limit:int=3072):
        self.base_url = base_url
        self.kernel_base_url = kernel_base_url
        self.model = model
        self.auth_token = auth_token
        self.limit = limit

    def is_too_long(self, chat_history:[dict]):
        """
        A heuristic method to estimate the tokens
        """
        return len(str(chat_history)) > self.limit

    async def get_available_llm(self):
        url = urljoin(self.kernel_base_url, "/v1.0/worker/list")
        
        loop = asyncio.get_running_loop()
        resp = await loop.run_in_executor(None, requests.get, url)
        if not resp.ok:
            return None
        llm = [executor for executor in reversed(resp.json()) if not re.match(r".*[-_b]qa.*", executor)]
        logger.debug(llm)
        llm.append(None)
        return llm[0]

    async def chat_complete(self, auth_token:str=None, messages:list=[], timeout=120):

        url = urljoin(self.base_url, "/v1.0/chat/completions")
        headers = {
            "Content-Type": "application/json",
            "Authorization": f"Bearer {self.auth_token if self.auth_token is not None else auth_token}",
        }
        model = self.model if self.model is not None else await self.get_available_llm()
        logger.debug(f"Use model {model}")
        request_body = {
            "messages": messages,
            "model": model,
        }

        with requests.post(url, headers=headers, json=request_body, stream=True, timeout=timeout) as resp:
            if not resp.ok:
                raise RuntimeError(f'Request failed with status {resp.status_code}')
            for line in resp.iter_lines(decode_unicode=True):
                if line == "event: close": break
                elif line.startswith("data: "):
                    chunk = json.loads(line[len("data: "):])["choices"][0]["delta"]["content"]
                    yield chunk

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
            llm = KuwaLlmClient(
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
                messages=[{"isbot":False, "content": llm_input}]
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