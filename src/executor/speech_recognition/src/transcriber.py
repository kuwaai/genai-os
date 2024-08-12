import os
import logging
import torch
import whisper_s2t
import functools
from whisper_s2t.backends.ctranslate2.hf_utils import download_model as whisper_s2t_download_model
from huggingface_hub.constants import HUGGINGFACE_HUB_CACHE

logger = logging.getLogger(__name__)

class WhisperS2tTranscriber:
    """
    Encapsulation of WhisperS2T process for multi-processing.
    """

    def __init__(self):
        pass

    @functools.lru_cache
    def load_model(self, name = None, backend = "CTranslate2", enable_word_ts:bool=False):
        if name is None: return None
        model_params = {"asr_options":{"word_timestamps": True}} if enable_word_ts else {}
        if os.path.isdir(name):
            model_path = name
        else:
            model_path = whisper_s2t_download_model(
                name,
                cache_dir=HUGGINGFACE_HUB_CACHE,
            )
        device="cuda" if torch.cuda.is_available() else "cpu"
        compute_type="float16" if torch.cuda.is_available() else "int8"
        logger.info(f"Using device {device}")
        model = whisper_s2t.load_model(
            model_identifier=model_path,
            backend=backend,
            device=device,
            compute_type=compute_type,
            **model_params
        )
        logger.debug(f"Model {name} loaded")
        return model
    
    def transcribe(
        self,
        model_name:str,
        model_backend:str="CTranslate2",
        model_params:dict=None,
        audio_files:list=[],
        **transcribe_kwargs
    ):
        logger.debug("Transcribing...")
        result = None
        try:
            enable_word_ts = model_params.get("word_timestamps", False)
            model = self.load_model(
                name=model_name,
                backend=model_backend,
                enable_word_ts=enable_word_ts,
            )
            if model_params is not None:
                model.update_params({'asr_options': model_params})
            result = model.transcribe_with_vad(audio_files, **transcribe_kwargs)

        except Exception:
            logger.exception("Error when generating transcription")

        logger.debug("Done transcribe.")
        return result