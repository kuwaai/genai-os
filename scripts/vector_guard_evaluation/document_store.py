import logging
import json
from typing import Tuple, List
from pathlib import Path
from langchain.docstore.document import Document
from langchain.embeddings import HuggingFaceEmbeddings
from langchain.vectorstores import FAISS
from langchain.schema.vectorstore import VectorStoreRetriever

class DocumentStore:
  """
  Encapsulation of the vector database and the embedding model.
  """

  config_filename = 'config.json'

  def __init__(self, embedding_model_name = 'thenlper/gte-large-zh'):
    """
    Initialize the document store.
    embedding_model: The sentence-transformers pre-trained model
      - 'paraphrase-multilingual-mpnet-base-v2' // Size: ~1.11GB
      - 'paraphrase-multilingual-MiniLM-L12-v2' // Size: ~471MB
      - 'infgrad/stella-base-zh' // Chinese embedding model // Size: ~210MB
    """
    self.logger = logging.getLogger(__name__)
    
    self.embedding_model_name = embedding_model_name
    self._load_embedding_model(embedding_model_name)
    self.vector_store:FAISS = None
  
  def _load_embedding_model(self, model_name):
    self.logger.info('Loading embedding model.')
    self.embedding_model = HuggingFaceEmbeddings(model_name=model_name)
    self.logger.info('Embedding model loaded.')

  def from_documents(self, docs: [Document]):
    """
    Construct document store from documents.
    """
    
    self.vector_store = FAISS.from_documents(docs, self.embedding_model)
  
  def from_embeddings(self, text_embeddings: [Tuple[str, List[float]]]):
    """
    Construct document store from text-embedding pairs
    """
    
    self.vector_store = FAISS.from_embeddings(text_embeddings, self.embedding_model)

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

    config = {}
    with open(Path(path)/DocumentStore.config_filename, 'r') as f:
      config = json.load(f)

    embedding_model_name = config['embedding_name']
    store = cls(embedding_model_name)
    store.vector_store = FAISS.load_local(path, store.embedding_model)
    return store

  def as_retriever(self,
               search_type:str='mmr',
               search_kwargs:dict={'k': 5, 'fetch_k': 10}) -> [Document]:
    """
    Retrieve the related chunks.
    """
    assert self.vector_store != None

    retriever = self.vector_store.as_retriever(
        search_type=search_type,
        search_kwargs=search_kwargs
    )
    return retriever
