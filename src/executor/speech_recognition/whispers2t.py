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
import torch
from functools import lru_cache
from threading import Thread

import whisper_s2t
from whisper_s2t.backends.ctranslate2.model import BEST_ASR_CONFIG
from whisper_s2t.utils import format_timestamp
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from kuwa.executor import LLMExecutor, Modelfile
from kuwa.executor.util import merge_config

logger = logging.getLogger(__name__)

class SpeechRecognitionExecutor(LLMExecutor):

    transcribe_param:dict = {
        "language": "auto"
    }
    param_prefix:str = "whisper_"
    default_model_name:str = "medium"
    default_model_backend:str = "CTranslate2"
    batch_size:int = 24

    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        model_group = parser.add_argument_group('Model Options')
        model_group.add_argument('--model', default=self.default_model_name, help='The model name')
        model_group.add_argument('--backend', default=self.default_model_backend, help='The model backend')
        model_group.add_argument('--batch_size', default=self.batch_size, type=int, help='The batch size')
        model_group.add_argument('--disable_timestamp', action="store_true", help='Do not output the timestamp.')
        transcribe_group = parser.add_argument_group('Transcribe Options')
        for param, value in BEST_ASR_CONFIG.items():
            if type(value) not in (str, int, float, bool, type(None)):
                continue
            transcribe_group.add_argument(
                f'--{param}',
                default=value,
                type=type(value)
            )

    def setup(self):

        self.default_model_name = self.args.model
        self.default_model_backend = self.args.backend
        self.load_model(self.default_model_name)
        # self.diarization_pipeline = Pipeline.from_pretrained(
        #     "pyannote/speaker-diarization-3.1"
        #     # "pyannote/speech-separation-ami-1.0"
        # )
        # self.diarization_pipeline.to(torch.device("cuda"))
        
        transcribe_param_arg = {
            k: getattr(self.args, k)
            for k in BEST_ASR_CONFIG.keys()
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
        user_records = list(filter(lambda x: x["role"] == "user", chat_history))
        for i, record in enumerate(reversed(user_records)):

            urls_in_msg = re.findall(r'^(https?://[^\s]+)$', record["content"])
            if len(urls_in_msg) != 0: 
                url = urls_in_msg[-1]
                begin_index = len(chat_history) - i - 1
                break
        
        return url, chat_history[begin_index:]

    @lru_cache
    def load_model(self, model_name = None, backend = "CTranslate2"):
        model = None
        if model_name is not None:
            model = whisper_s2t.load_model(
                model_identifier=model_name,
                backend=backend
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
    
    async def transcribe(self, filepath, model_name:str, param={}):
        logger.debug(f"Transcribe param: {param}")
        model = self.load_model(model_name)
        lang = param.pop("language")
        asr_options = merge_config(BEST_ASR_CONFIG, param)
        model.update_params({
            'asr_options': asr_options
        })

        result = model.transcribe_with_vad(
            [filepath],
            lang_codes=[lang],
            tasks=["transcribe"],
            initial_prompts=[None],
            batch_size=self.batch_size
        )

        logger.debug(f"Result: {result}")
        for segment in result[0]:
            yield segment

    async def llm_compute(self, history: list[dict], modelfile:Modelfile):

        filepath = None
        try:
            self.stop = False
            url, history = self.extract_last_url(history)
            if url is None: 
                raise ValueError("An URL to a audio file is expected.")

            filepath = self.download(url)

            transcribe_param = modelfile.parameters[self.param_prefix]
            logger.debug(f"{transcribe_param}")
            model_name = transcribe_param.pop("model", self.default_model_name)
            model_backend = transcribe_param.pop("backend", self.default_model_backend)
            disable_timestamp = transcribe_param.pop("disable_timestamp", self.disable_timestamp)
            transcribe_param = merge_config(self.transcribe_param, transcribe_param)

            result = self.transcribe(
                filepath=filepath,
                model_name=model_name,
                param=transcribe_param
            )
            async for segment in result:
                start_sec = segment["start_time"]
                end_sec = segment["end_time"]
                text = segment["text"]
                if not disable_timestamp:
                    yield "[{} ---> {}] {}\n".format(
                        format_timestamp(start_sec, always_include_hours=True),
                        format_timestamp(end_sec, always_include_hours=True),
                        text
                    )
                else:
                    yield "{}\n".format(text)
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
    executor = SpeechRecognitionExecutor()
    executor.run()