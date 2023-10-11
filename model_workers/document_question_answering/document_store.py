import logging
import asyncio
import functools
from langchain.docstore.document import Document
from parallel_splitter import ParallelSplitter
from langchain.embeddings import HuggingFaceEmbeddings
from langchain.vectorstores import FAISS
from langchain.retrievers.bm25 import BM25Retriever
from langchain.schema.vectorstore import VectorStoreRetriever
from langchain.retrievers.ensemble import EnsembleRetriever

class DocumentStore:
  def __init__(self, embedding_model = 'infgrad/stella-base-zh', mmr_k = 6, mmr_fetch_k = 12):
    """
    Initialize the document store.
    embedding_model: The sentence-transformers pre-trained model
      - 'paraphrase-multilingual-mpnet-base-v2' // Size: ~1.11GB
      - 'paraphrase-multilingual-MiniLM-L12-v2' // Size: ~471MB
      - 'infgrad/stella-base-zh' // Chinese embedding model // Size: ~210MB
    """
    self.logger = logging.getLogger(__name__)
    
    self.mmr_param = {
        'k': mmr_k,
        'fetch_k': mmr_fetch_k
    }
    # self.corase_k = 2

    self.splitter = ParallelSplitter(chunk_size=512, chunk_overlap=128)
    self.embedding_model = embedding_model
    self.vector_store:FAISS = None
    self.fine_retriever:VectorStoreRetriever = None
    # self.coarse_retriever:BM25Retriever = None
    # self.ensemble_retriever:EnsembleRetriever = None
  
  async def from_documents(self, docs: [Document]):
    
    # Chunking
    self.logger.info('Chunking...')
    chunks = await self.splitter.split(docs)
    self.logger.info('Got {} chunks.'.format(len(chunks)))

    # Embedding
    self.logger.info('Calculating embeddings...')
    loop = asyncio.get_running_loop()
    self.embeddings = await loop.run_in_executor(
      None,
      functools.partial(HuggingFaceEmbeddings, model_name=self.embedding_model)
    )
    self.logger.info('Embedding model loaded.')

    self.vector_store = await loop.run_in_executor(
      None,
      FAISS.from_documents,
      chunks,
      self.embeddings
    )
    self.logger.info('Embedding calculated.')

    # self.coarse_retriever = BM25Retriever.from_texts(chunks)
    # self.coarse_retriever.k = self.coarse_k
    self.fine_retriever = self.vector_store.as_retriever(
        search_typp='mmr',
        search_kwargs=self.mmr_param
    )
    # self.ensemble_retriever =ensemble_retriever = EnsembleRetriever(
    #   retrievers=[self.coarse_retriever, self.fine_retriever],
    #   weights=[0.5, 0.5]
    # )
  
  async def retrieve(self, question:str) -> [Document]:
    loop = asyncio.get_running_loop()
    related_docs = await loop.run_in_executor(
      None,
      self.fine_retriever.get_relevant_documents,
      question
    )
    return related_docs
