import logging
import asyncio
import functools
import json
from pathlib import Path
from langchain.docstore.document import Document
from langchain.embeddings import HuggingFaceEmbeddings
from langchain.vectorstores import FAISS
from langchain.retrievers.bm25 import BM25Retriever
from langchain.schema.vectorstore import VectorStoreRetriever
from langchain.retrievers.ensemble import EnsembleRetriever

from .parallel_splitter import ParallelSplitter

logger = logging.getLogger(__name__)

class DocumentStore:

  config_filename = 'config.json'

  def __init__(self, embedding_model = 'infgrad/stella-base-zh', mmr_k = 6, mmr_fetch_k = 12):
    """
    Initialize the document store.
    embedding_model: The sentence-transformers pre-trained model
      - 'paraphrase-multilingual-mpnet-base-v2' // Size: ~1.11GB
      - 'paraphrase-multilingual-MiniLM-L12-v2' // Size: ~471MB
      - 'infgrad/stella-base-zh' // Chinese embedding model // Size: ~210MB
    """
    
    self.mmr_param = {
        'k': mmr_k,
        'fetch_k': mmr_fetch_k
    }
    # self.corase_k = 2

    self.splitter = ParallelSplitter(chunk_size=512, chunk_overlap=128)
    self.embedding_model = embedding_model
    self.vector_store:FAISS = None
    self.fine_retriever:VectorStoreRetriever = None
    self.embeddings = None
    # self.coarse_retriever:BM25Retriever = None
    # self.ensemble_retriever:EnsembleRetriever = None
  
  def load_embedding_model(self):
    logger.info('Loading embedding model...')
    self.embeddings = HuggingFaceEmbeddings(model_name=self.embedding_model)
    logger.info('Embedding model loaded.')

  async def from_documents(self, docs: [Document]):
    
    loop = asyncio.get_running_loop()
    
    # Chunking
    logger.info('Chunking...')
    chunks = await self.splitter.split(docs)
    logger.info('Got {} chunks.'.format(len(chunks)))

    # Embedding
    if self.embeddings == None:
      await loop.run_in_executor(None, self.load_embedding_model)
    logger.info('Calculating embeddings...')
    self.vector_store = await loop.run_in_executor(
      None,
      FAISS.from_documents,
      chunks,
      self.embeddings
    )
    logger.info('Embedding calculated.')

    # self.coarse_retriever = BM25Retriever.from_texts(chunks)
    # self.coarse_retriever.k = self.coarse_k
    self.init_retriever()
    # self.ensemble_retriever =ensemble_retriever = EnsembleRetriever(
    #   retrievers=[self.coarse_retriever, self.fine_retriever],
    #   weights=[0.5, 0.5]
    # )

  def init_retriever(self):
    self.fine_retriever = self.vector_store.as_retriever(
        search_type='mmr',
        search_kwargs=self.mmr_param
    )

  def save(self, path:str):
    """
    Save the document store to local filesystem.
    """

    self.vector_store.save_local(path)
    
    config = {'embedding_name': self.embedding_model_name}
    with open(Path(path)/DocumentStore.config_filename, 'w') as f:
      json.dump(config, f)

  @classmethod
  def load(cls, path:str):
    """
    Load constructed document store from local filesystem.
    """

    logger.info('Loading document store...')
    config = {}
    with open(Path(path)/DocumentStore.config_filename, 'r') as f:
      config = json.load(f)

    embedding_model_name = config['embedding_name']
    store = cls(embedding_model=embedding_model_name)
    store.load_embedding_model()
    store.vector_store = FAISS.load_local(path, store.embeddings)
    store.init_retriever()
    logger.info('Document store loaded.')
    return store
  
  async def retrieve(self, question:str) -> [Document]:
    loop = asyncio.get_running_loop()
    related_docs = await loop.run_in_executor(
      None,
      self.fine_retriever.get_relevant_documents,
      question
    )
    return related_docs
