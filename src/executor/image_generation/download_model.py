#!/bin/python3

import logging
from diffusers import AutoPipelineForText2Image

logger = logging.getLogger(__name__)

if __name__ == '__main__':
    logging.basicConfig(level="INFO")

    model_name = 'stabilityai/stable-diffusion-2' 
    
    logger.info(f"Downloading model {model_name}")
    _ = AutoPipelineForText2Image.from_pretrained(model_name)
    logger.info(f"Done")