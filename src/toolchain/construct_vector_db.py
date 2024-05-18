#!/bin/python3

import argparse
import logging
import sys
import os
from dotenv import load_dotenv, find_dotenv
from langchain_community.document_loaders import DirectoryLoader
sys.path.append(".")

from lib.gpu_util import check_gpu
from lib.file_text_loader import FileTextLoader
from lib.parallel_splitter import ParallelSplitter
from lib.document_store import DocumentStore

logger = logging.getLogger(__name__)

def construct_db(
    docs_path:str,
    output_path:str,
    chunk_size:int = 512,
    chunk_overlap:int = 128,
    embedding_model:str = 'thenlper/gte-base-zh'
    ):
    """
    Construct vector database from local documents and save to the destination.
    """

    loader = None
    if os.path.isdir(docs_path):
        loader = DirectoryLoader(docs_path,
                            recursive=True,
                            loader_cls=FileTextLoader,
                            use_multithreading=True,
                            show_progress=True)
    else:
        loader = FileTextLoader(file_path=docs_path)
    logger.info(f'Loading documents...')
    docs = loader.load()
    logger.debug(docs)
    logger.info(f'Loaded {len(docs)} documents.')

    logger.info(f'Chunking documents...')
    splitter = ParallelSplitter(chunk_size=chunk_size, chunk_overlap=chunk_overlap)
    chunks = splitter.split(docs)
    logger.info(f'Chunked documents into {len(chunks)} chunks.')

    db = DocumentStore(embedding_model_name = embedding_model)
    logger.info(f'Constructing vector store...')
    db.from_documents(chunks)
    logger.info(f'Vector store constructed.')
    db.save(output_path)
    logger.info(f'Saved vector store to {output_path}.')

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Construct a FAISS vector database from local documents.')
    parser.add_argument("docs_path", help="the path to the directory of input documents.", type=str)
    parser.add_argument("output_path", help="the path where the final database will be stored.", type=str)
    parser.add_argument('--visible_gpu', default=None, help='Specify the GPU IDs that this executor can use. Separate by comma.')
    parser.add_argument("--chunk-size", help="The chunk size to split the document.", type=int, default=512)
    parser.add_argument("--chunk-overlap", help="The chunk size to split the document.", type=int, default=128)
    parser.add_argument("--embedding-model", help="the embedding model to use", type=str, default="thenlper/gte-base-zh")
    parser.add_argument("--log", help="the log level. (INFO, DEBUG, ...)", type=str, default="INFO")
    args = parser.parse_args()
    
    # Setup logger
    numeric_level = getattr(logging, args.log.upper(), None)
    if not isinstance(numeric_level, int):
        raise ValueError(f'Invalid log level: {args.log}')
    logging.basicConfig(level=numeric_level)
    
    # Read local .env file
    if args.visible_gpu:
        os.environ["CUDA_VISIBLE_DEVICES"] = args.visible_gpu
    check_gpu()
    
    construct_db(
        docs_path=args.docs_path,
        output_path=args.output_path,
        chunk_size=args.chunk_size,
        chunk_overlap=args.chunk_overlap,
        embedding_model=args.embedding_model
    )