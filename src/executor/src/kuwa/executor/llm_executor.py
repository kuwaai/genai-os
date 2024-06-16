import re
import json
import logging
from .base_executor import BaseExecutor
from .modelfile import Modelfile

logger = logging.getLogger(__name__)

class LLMExecutor(BaseExecutor):
    """
    The specialized class for serving LLM process.
    """

    async def serve(self, header, content):
        param = dict(content)
        history = json.loads(param.pop("input", "[]"))
        history = to_openai_chat_format(history)
        history = rectify_chat_history(history)
        modelfile = Modelfile.from_json(param.pop("modelfile", "[]"))
        modelfile.parameters["_lang"] = header.get("Accept-Language")
        for k, v in param.items():
            modelfile.parameters[f"_{k}"] = v
        
        logger.debug(f"History: {history}")
        logger.debug(f"Modelfile: {modelfile}")
        async for chunk in self.llm_compute(history=history, modelfile=modelfile):
            yield chunk

    async def llm_compute(self, history: list[dict], modelfile:Modelfile):
        raise NotImplementedError("LLM Executor should implement the \"llm_compute\" method.")

def to_openai_chat_format(history: list[dict]):
    """
    Convert the chat history from Kuwa's format to OpenAI's format.
    """
    history = [
        {
            "role": "assistant" if i["isbot"] else "user",
            "content": i["msg"]
        }
        for i in history
    ]
    return history

def rectify_chat_history(history: list[dict]):
    """
    Ensure the history begin with "user."
    """
    if len(history)==0: return history
    first_user_idx = 0
    while history[first_user_idx]["role"] != "user" and first_user_idx+1 < len(history)-1:
        first_user_idx += 1
    history = history[first_user_idx:]
    return history

def extract_last_url(chat_history: list[dict]) -> (str, list[dict]):
    """
    Find the latest URL provided by the user and trim the chat history to there.
    Note: the input is OpenAI chat format.
    """

    url_regex = r'(https?://[^\s]+)'
    url = None
    begin_index = 0
    for i, record in enumerate(reversed(chat_history)):
        if record["role"] != "user":
            continue

        urls_in_msg = re.findall(url_regex, record["content"])
        if len(urls_in_msg) != 0:
            url = urls_in_msg[-1]
            begin_index = len(chat_history) - i - 1
            break
    
    logger.debug("URL: {}\nFrom message: {}".format(url, chat_history[begin_index]["content"]))
    trimmed_chat_history = list(chat_history[begin_index:])
    trimmed_chat_history[0]["content"] = re.sub(url_regex, '', trimmed_chat_history[0]["content"]).strip()

    return url, trimmed_chat_history

if __name__ == "__main__":
    executor = LLMExecutor()
    executor.run()