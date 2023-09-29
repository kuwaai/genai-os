#!/bin/python3
# -#- coding: UTF-8 -*-

from model_api_server.datatype import ChatRecord, Role
from model_api_server.interfaces import GeneralProcessInterface
from typing import Generator
from recursive_url_multimedia_loader import RecursiveUrlMultimediaLoader
from document_store import DocumentStore

import re
import logging

def extract_last_url(chat_history: [ChatRecord]):
    """
    Find the latest URL provided by the user.
    """

    urls = []
    for user_input in filter(lambda x: x.role == Role.USER, chat_history):
      urls_in_msg = re.findall(r'(https?://[^\s]+)', user_input.msg)
      if len(urls_in_msg) != 0: 
        urls.append(*urls_in_msg)
    return urls[-1]

class DocumentQaProcess(GeneralProcessInterface):
  def __init__(self):
    self.logger = logging.getLogger(__name__)

  async def process(self, chat_history: [ChatRecord]) -> Generator[str, None, None]:
    
    # Extract context
    final_user_input = next(filter(lambda x: x.role == Role.USER, reversed(chat_history))) 
    url = extract_last_url(chat_history)

    # Fetching documents
    self.logger.info('Fetching URL "{}"'.format(url))
    loader = RecursiveUrlMultimediaLoader(
      url=url,
      max_depth=2,
      prevent_outside=False,
      use_async = True,
      cache_proxy_url = 'http://web_cache:10250'
    ) 
    docs = await loader.async_load()
    self.logger.info('Fetched {} documents.'.format(len(docs)))
    
    document_store = DocumentStore()
    document_store.from_documents(docs)
    
    question = "碩士班畢業條件為何？"
    related_docs = document_store.retrieve(question)
    
    for doc in related_docs:
      yield str(doc)+'\n'
