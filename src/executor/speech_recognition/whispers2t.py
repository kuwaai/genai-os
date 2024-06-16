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
import concurrent.futures
import functools
import multiprocessing as mp
from whisper_s2t.backends.ctranslate2.model import BEST_ASR_CONFIG
from whisper_s2t.utils import format_timestamp

from kuwa.executor import LLMExecutor, Modelfile
from kuwa.executor.llm_executor import extract_last_url
from kuwa.executor.util import merge_config

sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from src.speaker_diarization import PyannoteSpeakerDiarizer
from src.transcriber import WhisperS2tTranscriber

logger = logging.getLogger(__name__)

class SpeechRecognitionExecutor(LLMExecutor):

    transcribe_param:dict = {}
    param_prefix:str = "whisper_"
    default_model_name:str = "medium"
    default_model_backend:str = "CTranslate2"
    language:str = "en"
    batch_size:int = 24
    diar_thld_sec:float = 1.0
    disable_timestamp:bool = False
    disable_diarization:bool = False

    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        model_group = parser.add_argument_group('Model Options')
        model_group.add_argument('--model', default=self.default_model_name, help='The model name.')
        model_group.add_argument('--backend', default=self.default_model_backend, help='The model backend.')
        model_group.add_argument('--language', default=self.language, help='The language to transcribe.')
        model_group.add_argument('--batch_size', default=self.batch_size, type=int, help='The batch size')
        model_group.add_argument('--disable_timestamp', action="store_true", help='Do not output the timestamp.')
        model_group.add_argument('--disable_diarization', action="store_true", help='Disable speaker diarization annotation.')
        model_group.add_argument('--diar_thld_sec', default=self.diar_thld_sec, type=float, help='The threshold of diarization in seconds.')
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
        self.language = self.args.language
        self.batch_size = self.args.batch_size
        self.diar_thld_sec = self.args.diar_thld_sec
        
        transcribe_param_arg = {
            k: getattr(self.args, k)
            for k in BEST_ASR_CONFIG.keys()
            if f"--{k}" in sys.argv
        }
        self.transcribe_param = merge_config(self.transcribe_param, transcribe_param_arg)
        self.disable_timestamp = self.args.disable_timestamp
        self.disable_diarization = self.args.disable_diarization

        self.stop = False
        
        # Initialize the pipelines
        self.transcriber = WhisperS2tTranscriber()
        if not self.disable_diarization:
            self.diarizer = PyannoteSpeakerDiarizer()

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
    
    async def transcribe(self, filepath, model_name:str, model_backend:str, lang:str, prompt:str=None, param={}):
        logger.debug(f"Transcribe param: {param}")
        
        model_params = merge_config(BEST_ASR_CONFIG, param)

        # Execute in current process to avoid load the model again.
        loop = asyncio.get_event_loop()
        with concurrent.futures.ThreadPoolExecutor(max_workers=1) as pool:
            job = functools.partial(
                self.transcriber.transcribe,
                model_name=model_name,
                model_backend=model_backend,
                model_params=model_params,
                audio_files=[filepath],
                lang_codes=[lang],
                tasks=["transcribe"],
                initial_prompts=[prompt],
                batch_size=self.batch_size
            )
            result = await loop.run_in_executor(pool, job)

        return result[0]

    async def speaker_diarization(self, filepath:str, num_speakers:int, diar_thld_sec:float):
        self.diarizer = self.diarizer if hasattr(self, "diarizer") else PyannoteSpeakerDiarizer()
        logger.debug(f"num_speakers={num_speakers}")
        logger.debug(f"diar_thld_sec={diar_thld_sec}")
        
        loop = asyncio.get_event_loop()
        mp_context = mp.get_context("spawn") # Torch with CUDA needs a "spawn" context to work
        with concurrent.futures.ProcessPoolExecutor(max_workers=1, mp_context=mp_context) as pool:
            job = functools.partial(
                self.diarizer.diarization,
                src_audio_file=filepath,
                num_speakers=num_speakers,
                duration_thld_sec=diar_thld_sec
            )
            result = await loop.run_in_executor(pool, job)

        return result

    def _format_output(self, segment:dict, output_timestamp:bool=False):
        start_sec = segment["start_time"]
        end_sec = segment["end_time"]
        text = segment["text"].strip()
        if text == "": return ""
        speaker = "{}: ".format(", ".join(segment['speaker'])) if "speaker" in segment else ""
        
        result = "{}{}\n".format(speaker, text)
        if output_timestamp:
            result = "[{} ---> {}] {}\n".format(
                format_timestamp(start_sec, always_include_hours=True),
                format_timestamp(end_sec, always_include_hours=True),
                result
            )
        return result

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
            transcribe_param = merge_config(self.transcribe_param, transcribe_param)
            logger.debug(f"{transcribe_param}")

            # Extract parameters
            prompt = modelfile.override_system_prompt
            model_name = transcribe_param.pop("model", self.default_model_name)
            model_backend = transcribe_param.pop("backend", self.default_model_backend)
            disable_timestamp = transcribe_param.pop("disable_timestamp", self.disable_timestamp)
            disable_diarization = transcribe_param.pop("disable_diarization", self.disable_diarization)
            lang = transcribe_param.pop("language", self.language)
            num_speakers = self.read_param_from_history(history=history, param="speakers", type=int)
            diar_thld_sec = transcribe_param.pop("diar_thld_sec", self.diar_thld_sec)

            transcribe_param["word_timestamps"] = not disable_diarization
            transcribe_job = self.transcribe(
                filepath=src_file,
                model_name=model_name,
                model_backend=model_backend,
                prompt=prompt,
                lang=lang,
                param=transcribe_param
            )
            if disable_diarization:
                result = await transcribe_job
            else:
                diarization_job = self.speaker_diarization(
                    filepath=src_file,
                    num_speakers=num_speakers,
                    diar_thld_sec=diar_thld_sec
                )
                result, diary = await asyncio.gather(transcribe_job, diarization_job)
                result = [
                    {
                        "start_time": word["start"],
                        "end_time": word["end"],
                        "text": ("" if lang == "zh" else " ") + word["word"]
                    }
                    for segment in result for word in segment["word_timestamps"]
                ]
                result = diary.annotate_transcript(result)

            logger.debug(f"Final Result: {result}")
            output = "".join([self._format_output(i, not disable_timestamp) for i in result])

            yield output
            
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