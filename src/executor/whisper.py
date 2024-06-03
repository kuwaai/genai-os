import os
import re
import sys
import json
import asyncio
import logging
import requests
import tempfile
import queue
import mimetypes
import argparse
from functools import lru_cache
from threading import Thread
from pywhispercpp.model import Model as WhisperModel
from pywhispercpp.utils import to_timestamp
from pywhispercpp.constants import PARAMS_SCHEMA as WHISPER_PARAM_SCHEMA
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from kuwa.executor import LLMExecutor, Modelfile
from kuwa.executor.util import merge_config

logger = logging.getLogger(__name__)

from pywhispercpp.model import Model

class TerminatedError(Exception):
    pass

class WhisperExecutor(LLMExecutor):

    transcribe_param:dict = {
        "language": "auto"
    }
    param_prefix:str = "whisper_"

    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        model_group = parser.add_argument_group('Model Options')
        model_group.add_argument('--model', default="medium", help='The model name')
        model_group.add_argument('--disable_timestamp', action="store_true", help='Do not output the timestamp.')
        transcribe_group = parser.add_argument_group('Transcribe Options')
        for param, schema in WHISPER_PARAM_SCHEMA.items():
            transcribe_group.add_argument(
                f'--{param}',
                default=self.transcribe_param.get(param, schema['default']),
                type=schema['type'],
                help=schema['description']
            )

    def setup(self):
        if not self.LLM_name:
            self.LLM_name = "whisper"

        self.default_model_name = self.args.model
        self.load_model(self.default_model_name)
        
        transcribe_param_arg = {
            k: getattr(self.args, k)
            for k in WHISPER_PARAM_SCHEMA.keys()
            if f"--{k}" in sys.argv
        }
        self.transcribe_param = merge_config(self.transcribe_param, transcribe_param_arg)
        self.disable_timestamp = self.args.disable_timestamp

        self.stop = False

    def extract_last_url(self, chat_history: list):
        """
        Find the latest URL provided by the user and trim the chat history to there.
        """

        url = None
        begin_index = 0
        user_records = list(filter(lambda x: not x["isbot"], chat_history))
        for i, record in enumerate(reversed(user_records)):

            urls_in_msg = re.findall(r'^(https?://[^\s]+)$', record["msg"])
            if len(urls_in_msg) != 0: 
                url = urls_in_msg[-1]
                begin_index = len(chat_history) - i - 1
                break
        
        return url, chat_history[begin_index:]

    @lru_cache
    def load_model(self, model_name = None):
        model = None
        if model_name is not None:
            model = WhisperModel(
                model_name,
                print_progress=True,
                translate=False,
                log_level=self.log_level
            )

        return model

    def download(self, url):
        # Create a temporary file to store the downloaded content
        filepath = None
        response = requests.get(url)
        content_type = response.headers["Content-Type"]
        logger.debug(f"Content-Type: {content_type}")
        extension = mimetypes.guess_extension(content_type.split(';', 1)[0])
        with tempfile.NamedTemporaryFile(delete=False, suffix=extension) as f:
            f.write(response.content)
            filepath = f.name
        return filepath
    
    async def transcribe(self, filepath, model_name:str, param=None):
        model = self.load_model(model_name)

        q = queue.Queue() # whisper produces, the generator consumes
        job_done = object() # signals the processing is done

        # Producer
        def on_new_segment(segments):
            for s in segments:
                q.put(s)
            if self.stop:
                raise TerminatedError
        def producer_task():
            try:
                model.transcribe(filepath, new_segment_callback=on_new_segment, **param)
            except TerminatedError:
                pass
            except Exception as e:
                logger.exception("Error during transcribing.")
            finally:
                q.put(job_done)

        producer_thread = Thread(target=producer_task)
        producer_thread.start()

        # Consumer
        while True:
            try:
                next_item = q.get(block=False) # Blocks until an input is available
            except queue.Empty:
                continue
            if next_item is job_done:
                break
            yield next_item

        producer_thread.join()

    async def llm_compute(self, data):

        filepath = None
        try:
            self.stop = False
            chat_history = json.loads(data.get("input"))
            modelfile = Modelfile.from_json(data.get("modelfile", "[]"))
            url, chat_history = self.extract_last_url(chat_history)
            if url is None: 
                raise ValueError("An URL to a audio file is expected.")

            filepath = self.download(url)

            transcribe_param = modelfile.parameters[self.param_prefix]
            logger.debug(f"{transcribe_param}")
            model_name = transcribe_param.pop("model", self.default_model_name)
            disable_timestamp = transcribe_param.pop("disable_timestamp", self.disable_timestamp)
            transcribe_param = merge_config(self.transcribe_param, transcribe_param)

            result = self.transcribe(
                filepath=filepath,
                model_name=model_name,
                param=transcribe_param
            )
            async for segment in result:
                if not disable_timestamp:
                    yield "[{} ---> {}] {}\n".format(
                        to_timestamp(segment.t0), to_timestamp(segment.t1), segment.text
                    )
                else:
                    yield "{}\n".format(segment.text)
                if self.stop: break
        except Exception as e:
            logger.exception("Error occurs during generation.")
            yield str(e)
        finally:
            self.stop = False
            if filepath is not None:
                os.remove(filepath)
            logger.debug("finished")

    async def abort(self):
        self.stop = True
        logger.debug("aborted")
        return "Aborted"

if __name__ == "__main__":
    executor = WhisperExecutor()
    executor.run()