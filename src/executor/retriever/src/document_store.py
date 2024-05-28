import logging
import asyncio
import functools
import json
from pathlib import Path
from langchain.docstore.document import Document
from langchain_community.embeddings import HuggingFaceEmbeddings
from langchain_community.vectorstores import FAISS
from langchain.retrievers.bm25 import BM25Retriever
from langchain.schema.vectorstore import VectorStoreRetriever
from langchain.retrievers.ensemble import EnsembleRetriever

from .parallel_splitter import ParallelSplitter
from .embedding_model_store import EmbeddingModelStore

logger = logging.getLogger(__name__)

class DocumentStore:

    config_filename = 'config.json'

    def __init__(self, vector_store:FAISS, embedding_model:HuggingFaceEmbeddings=None):
        self.vector_store:FAISS = vector_store
        self.embedding_model = embedding_model

    def get_retriever(self, search_type:str='mmr', **kwargs):
        fine_retriever:VectorStoreRetriever = self.vector_store.as_retriever(
            search_type=search_type,
            **kwargs
        )
        return fine_retriever

    def save(self, path:str):
        """
        Save the document store to local filesystem.
        """

        self.vector_store.save_local(path)
        
        config = {'embedding_name': self.embedding_model.model_name}
        with open(Path(path)/DocumentStore.config_filename, 'w') as f:
            json.dump(config, f)

    @classmethod
    def load(cls, path:str, embedding_model_store:EmbeddingModelStore=EmbeddingModelStore()):
        """
        Load constructed document store from local filesystem.
        """

        logger.info('Loading document store...')
        config = {}
        with open(Path(path)/DocumentStore.config_filename, 'r') as f:
            config = json.load(f)

        embedding_model_name = config['embedding_name']
        store = cls(embedding_model=embedding_model_store.load_model(embedding_model_name))
        store.vector_store = FAISS.load_local(path, store.embeddings, allow_dangerous_deserialization=True)
        logger.info('Document store loaded.')
        return store

class DocumentStoreFactory:
    def __init__(
        self,
        embedding_model:HuggingFaceEmbeddings = None,
        splitter = None,
        ):
        
        self.splitter = splitter
        self.embedding_model = embedding_model
    
    async def from_documents(self, docs: [Document]):
        
        loop = asyncio.get_running_loop()
        
        # Chunking
        logger.info('Chunking...')
        chunks = await self.splitter.split(docs)
        logger.info('Got {} chunks.'.format(len(chunks)))

        # Embedding
        logger.info('Calculating embeddings...')
        vector_store = await loop.run_in_executor(
            None,
            FAISS.from_documents,
            chunks,
            self.embedding_model
        )
        logger.info('Embedding calculated.')

        return DocumentStore(vector_store, self.embedding_model)
