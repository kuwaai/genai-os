#!/bin/python3

import logging
from langchain_community.embeddings import HuggingFaceEmbeddings

logger = logging.getLogger(__name__)

if __name__ == '__main__':
    logging.basicConfig(level="INFO")

    model_name = 'intfloat/multilingual-e5-small' 
    
    logger.info(f"Downloading model {model_name}")
    _ = HuggingFaceEmbeddings(model_name=model_name)
    logger.info(f"Done")