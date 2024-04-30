import os
import itertools
from multiprocessing import Pool, cpu_count
from langchain.text_splitter import RecursiveCharacterTextSplitter
from langchain.docstore.document import Document

class SplitDocumentJob(object):
  """
  Encapsulate the context of document splitting job.
  """

  def __init__(self, chunk_size=128, chunk_overlap = 16):
    self.chunk_size = chunk_size
    self.chunk_overlap = chunk_overlap

  def __call__(self, doc):
    splitter = RecursiveCharacterTextSplitter(
      chunk_size = self.chunk_size,
      chunk_overlap  = self.chunk_overlap,
      length_function = len,
      add_start_index = True,
    )
    return splitter.split_documents([doc]) 

class ParallelSplitter:
  """
  Split the documents.
  Since the splitter only uses CPU, we speedup the process with multi processing.
  """

  def __init__(self, chunk_size:int=128, chunk_overlap:int=16):
    self.chunk_overlap = chunk_overlap
    self.chunk_size = chunk_size

  def split(self, docs: [Document]):

    chunked_docs = []
    pool = Pool(cpu_count())
    split_job = SplitDocumentJob(self.chunk_size, self.chunk_overlap)
    chunked_docs = pool.map(split_job, docs)
    
    chunks = list(itertools.chain(*chunked_docs)) # Flatten

    return chunks