import os
import multiprocessing
import itertools
from langchain.text_splitter import RecursiveCharacterTextSplitter
from langchain.text_splitter import SentenceTransformersTokenTextSplitter
from langchain.docstore.document import Document

class SplitDocumentJob(object):
    def __init__(self, chunk_size=128, chunk_overlap = 16):
    #   self.splitter = SentenceTransformersTokenTextSplitter(chunk_overlap=chunk_overlap)
      self.splitter = RecursiveCharacterTextSplitter(
            # Set a really small chunk size, just to show.
            chunk_size = chunk_size,
            chunk_overlap  = chunk_overlap,
            length_function = len,
            add_start_index = True,
        )
    def __call__(self, doc):
      return self.splitter.split_documents([doc]) 

class ParallelSplitter:
  """
  Split the documents.
  Since the tokenizer only uses CPU, we speedup the process with multi processing.
  """

  def __init__(self, chunk_size:int=128, chunk_overlap:int=16):
    self.chunk_overlap = chunk_overlap
    self.chunk_size = chunk_size

  def split(self, docs: [Document]):
    threads = multiprocessing.cpu_count()
    thread_pool = multiprocessing.Pool(threads)

    # Disable original tokenizer since they may cause deadlock
    # Ref: https://stackoverflow.com/a/67254879
    os.environ['TOKENIZERS_PARALLELISM'] = 'false'
    chunked_docs = thread_pool.map(SplitDocumentJob(self.chunk_size, self.chunk_overlap), docs)
    chunks = list(itertools.chain(*chunked_docs)) # Flatten

    return chunks