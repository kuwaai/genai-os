#!/bin/python3

import argparse
import logging
import json
import glob
import os
from dotenv import load_dotenv, find_dotenv

from document_store import DocumentStore

logger = logging.getLogger(__name__)

def load_jsonls(dir_path:str):
    data = []
    for file_path in glob.glob(f'{dir_path}/*.jsonl'):
        file_path = os.path.abspath(file_path)
        with open(file_path, 'r') as f:
            data += [json.loads(l) for l in f]
    return data

def load_txts(dir_path:str):
    data = []
    for file_path in glob.glob(f'{dir_path}/*.txt'):
        file_path = os.path.abspath(file_path)
        with open(file_path, 'r') as f:
            data += [{'text': l} for l in f]
    return data

def split(data:str, chunk_size=512):
    n = chunk_size
    if data is None: return []
    return [data[i:i+n] for i in range(0, len(data), n)]

def get_indexes(data:dict):
    indexes = ['text', 'title', 'summarization']
    indexes = [
        chunk
        for k in indexes
        for chunk in split(data.get(k))
    ]
    indexes = list(filter(None, indexes))

    return indexes

def construct_db(dataset_path: str, output_path: str):
    """
    Construct vector database from preprocessed dataset and save to the destination.
    """

    dataset = []
    embeddings = []

    logger.info(f'Loading documents...')
    dataset += load_jsonls(dataset_path)
    dataset += load_txts(dataset_path)
    logger.info(f'Loaded {len(dataset)} documents.')
    indexes = [idx for record in dataset for idx in get_indexes(record)]
    logger.info(f'Loaded {len(indexes)} indexes.')

    db = DocumentStore()
    logger.info(f'Calculating embeddings...')
    embeddings = db.embedding_model.embed_documents(indexes)
    logger.info(f'Embedding calculated.')

    logger.info(f'Constructing vector store...')
    text_embedding_pairs = [('', embedding) for embedding in embeddings]
    db.from_embeddings(text_embedding_pairs)
    logger.info(f'Vector store constructed.')
    db.save(output_path)
    logger.info(f'Saved vector store to {output_path}.')

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Construct a FAISS vector database from local documents.')
    parser.add_argument("dataset_path", help="the path to the directory of the dataset (JSONL format).", type=str)
    parser.add_argument("output_path", help="the path where the final database will be stored.", type=str)
    parser.add_argument("--log", help="the log level. (INFO, DEBUG, ...)", type=str, default="INFO")
    args = parser.parse_args()
    
    # Setup logger
    numeric_level = getattr(logging, args.log.upper(), None)
    if not isinstance(numeric_level, int):
        raise ValueError(f'Invalid log level: {args.log}')
    logging.basicConfig(level=numeric_level)
    
    # Read local .env file
    load_dotenv(find_dotenv())
    
    construct_db(args.dataset_path, args.output_path)