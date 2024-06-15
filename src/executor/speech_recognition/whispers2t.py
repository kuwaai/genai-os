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
import whisper_s2t
from functools import lru_cache
from threading import Thread
from whisper_s2t.backends.ctranslate2.model import BEST_ASR_CONFIG
from whisper_s2t.utils import format_timestamp

from kuwa.executor import LLMExecutor, Modelfile
from kuwa.executor.llm_executor import extract_last_url
from kuwa.executor.util import merge_config

sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from src.speaker_diarization import PyannoteSpeakerDiarization

logger = logging.getLogger(__name__)

class SpeechRecognitionExecutor(LLMExecutor):

    transcribe_param:dict = {
        "language": "en"
    }
    param_prefix:str = "whisper_"
    default_model_name:str = "medium"
    default_model_backend:str = "CTranslate2"
    batch_size:int = 24
    disable_timestamp:bool = False
    disable_diarization:bool = False

    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        model_group = parser.add_argument_group('Model Options')
        model_group.add_argument('--model', default=self.default_model_name, help='The model name')
        model_group.add_argument('--backend', default=self.default_model_backend, help='The model backend')
        model_group.add_argument('--batch_size', default=self.batch_size, type=int, help='The batch size')
        model_group.add_argument('--disable_timestamp', action="store_true", help='Do not output the timestamp.')
        model_group.add_argument('--disable_diarization', action="store_true", help='Disable speaker diarization annotation.')
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

        os.environ["KMP_DUPLICATE_LIB_OK"]="TRUE"
        self.default_model_name = self.args.model
        self.default_model_backend = self.args.backend
        self.batch_size = self.args.batch_size
        self.load_model(self.default_model_name)
        if not self.disable_diarization:
            self.separator = PyannoteSpeakerDiarization()
        
        transcribe_param_arg = {
            k: getattr(self.args, k)
            for k in BEST_ASR_CONFIG.keys()
            if f"--{k}" in sys.argv
        }
        self.transcribe_param = merge_config(self.transcribe_param, transcribe_param_arg)
        self.disable_timestamp = self.args.disable_timestamp
        self.disable_diarization = self.args.disable_diarization

        self.stop = False

    @lru_cache
    def load_model(self, model_name = None, backend = "CTranslate2"):
        model = None
        asr_options = BEST_ASR_CONFIG
        model_kwargs = {}
        if not self.disable_diarization:
            model_kwargs["asr_options"] = {}
            model_kwargs["asr_options"]["word_timestamps"] = True
        if model_name is not None:
            model = whisper_s2t.load_model(
                model_identifier=model_name,
                backend=backend,
                **model_kwargs
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

    def read_param_from_history(self, history:[dict], param:str, type=None, default=None):
        value = default
        target = f"/{param}"
        for record in reversed(history):
            if record["role"] != "user": continue
            if record["content"].startswith(target):
                value = record["content"][len(target):]
        return type(value) if type is not None and value is not None else value
    
    async def transcribe(self, filepath, model_name:str, param={}):
        logger.debug(f"Transcribe param: {param}")
        model = self.load_model(model_name)
        lang = param.pop("language")
        disable_diarization = param.pop("disable_diarization", self.disable_diarization)
        
        asr_options = merge_config(BEST_ASR_CONFIG, param)
        asr_options["word_timestamps"] = not disable_diarization
        model.update_params({'asr_options': asr_options})

        result = model.transcribe_with_vad(
            [filepath],
            lang_codes=[lang],
            tasks=["transcribe"],
            initial_prompts=[None],
            batch_size=self.batch_size
        )

        return result[0]

    async def llm_compute(self, history: list[dict], modelfile:Modelfile):

        src_file = None
        gc_paths = []
        try:
            self.stop = False
            url, history = extract_last_url(history)
            if url is None: 
                raise ValueError("An URL to a audio file is expected.")

            src_file = self.download(url)
            gc_paths.append(src_file)

            transcribe_param = modelfile.parameters[self.param_prefix]
            logger.debug(f"{transcribe_param}")
            model_name = transcribe_param.pop("model", self.default_model_name)
            model_backend = transcribe_param.pop("backend", self.default_model_backend)
            disable_timestamp = transcribe_param.pop("disable_timestamp", self.disable_timestamp)
            disable_diarization = transcribe_param.pop("disable_diarization", self.disable_diarization)
            transcribe_param = merge_config(self.transcribe_param, transcribe_param)

            diary = None
            if not disable_diarization:
                self.separator = self.separator if hasattr(self, "separator") else PyannoteSpeakerSeparator()
                num_speakers = self.read_param_from_history(history=history, param="speakers", type=int)
                logger.debug(f"num_speakers={num_speakers}")
                logger.debug("Diarizing..")
                
                # tmp_dir = tempfile.mkdtemp()
                # gc_paths.append(tmp_dir)
                # logger.debug(f'Created temporary directory {tmp_dir}')
                # files = await self.separator.separate(
                #     src_audio_file=src_file,
                #     output_dir=tmp_dir,
                #     num_speakers=num_speakers
                # )
                # logger.debug(files)

                diary = await self.separator.diarization(
                    src_audio_file=src_file,
                    num_speakers=num_speakers
                )
                logger.debug(diary)

                logger.debug("Done diarization")
                logger.debug(f"Diary: {diary}")
            
            result = await self.transcribe(
                filepath=src_file,
                model_name=model_name,
                param=transcribe_param
            )
            if diary is not None:
                result = [
                    {
                        "start_time": word["start"],
                        "end_time": word["end"],
                        "text": word["word"]
                    }
                    for segment in result for word in segment["word_timestamps"]
                ]
                result = diary.annotate_transcript(result)

            logger.debug(f"Result: {result[0]}")
            for segment in result:
                start_sec = segment["start_time"]
                end_sec = segment["end_time"]
                text = segment["text"].strip()
                if text == "": continue
                speaker = "{}: ".format(", ".join(segment['speaker'])) if "speaker" in segment else ""
                if not disable_timestamp:
                    yield "[{} ---> {}] {}{}\n".format(
                        format_timestamp(start_sec, always_include_hours=True),
                        format_timestamp(end_sec, always_include_hours=True),
                        speaker,
                        text
                    )
                else:
                    yield "{}{}\n".format(speaker, text)
                if self.stop: break
        except Exception as e:
            logger.exception("Error occurs during generation.")
            yield str(e)
        finally:
            self.stop = False
            for path_to_delete in gc_paths:
                if os.path.isfile(path_to_delete):
                    os.remove(path_to_delete)
                elif os.path.isdir(path_to_delete):
                    os.rmdir(path_to_delete)
            logger.debug("finished")

    async def abort(self):
        self.stop = True
        logger.debug("aborted")
        return "Aborted"

if __name__ == "__main__":
    executor = SpeechRecognitionExecutor()
    executor.run()