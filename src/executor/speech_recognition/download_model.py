#!/bin/python3

import logging
import argparse
import whisper_s2t
import pyannote.audio

logger = logging.getLogger(__name__)

def load_whisper(model_name = "medium", model_backend = "CTranslate2"):
    logger.info(f"Downloading model {model_name}")
    _ = whisper_s2t.load_model(
        model_identifier=model_name,
        backend=model_backend,
        compute_type="float32"
    )
    logger.info(f"Done")

def load_diarizer(pipeline_name = "pyannote/speaker-diarization-3.1"):
    logger.info(f"Downloading pipeline {pipeline_name}")
    _ = pyannote.audio.Pipeline.from_pretrained(pipeline_name)
    logger.info(f"Done")

if __name__ == '__main__':
    logging.basicConfig(level="INFO")

    parser = argparse.ArgumentParser()
    parser.add_argument("--diarizer", action="store_true", help="Additionally, download the diarizer.")
    args = parser.parse_args()

    load_whisper()
    if args.diarizer:
        load_diarizer()