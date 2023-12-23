#!/bin/python3
# -#- coding: UTF-8 -*-

from worker_framework.datatype import ChatRecord, Role
from typing import Generator, Iterable
from pathlib import Path
from urllib.error import HTTPError
from langchain.docstore.document import Document

from .recursive_url_multimedia_loader import RecursiveUrlMultimediaLoader
from .document_store import DocumentStore
from .taide_llm import TaideLlmFactory

import re
import gc
import os
import torch
import logging
import chevron
import asyncio
import copy
import jsonlines
import time

class DocumentQa:

  log_path = os.environ.get('llm_log_path','/var/log/doc_qa/qa.jsonl')

  def __init__(self, document_store:str = None):
    self.logger = logging.getLogger(__name__)
    self.llm = TaideLlmFactory.get_taide_llm(model_location=os.environ.get('MODEL_LOCATION','remote-nchc'))
    self.document_store:DocumentStore = None
    if document_store != None:
      self.document_store = DocumentStore.load(document_store)
  
  def generate_llm_input(self, task, question, related_docs):
    
    template_path = 'prompt_template/' + f'llm_input_{task}.mustache'
    llm_input_template = Path(template_path).read_text(encoding="utf8")
    llm_input = chevron.render(llm_input_template, {
      'docs': related_docs,
      'question': question
    })

    return llm_input

  def replace_chat_history(self, chat_history, task, question, related_docs):
    llm_input = self.generate_llm_input(task, question, related_docs)
    modified_chat_history = chat_history[:-1] + [ChatRecord(llm_input, Role.USER)]
    if modified_chat_history[0].msg is None:
      if len(modified_chat_history) != 2: # Multi-round
        modified_chat_history[0].msg = '請提供這篇文章的摘要'
      else: # Single-round
        modified_chat_history = modified_chat_history[1:]
    modified_chat_history = [
      ChatRecord('[Empty message]', r.role) if r.msg == '' else r
      for r in modified_chat_history
    ]

    return modified_chat_history
  
  def is_english(self, paragraph:str, threshold=0.8):
    total_count = len(paragraph)
    english_charter_count = len(paragraph.encode("ascii", "ignore"))
    english_rate = 0 if total_count == 0 else english_charter_count / total_count

    return english_rate >= threshold

  def get_final_user_input(self, chat_history: [ChatRecord]) -> str:
    final_user_record = next(filter(lambda x: x.role == Role.USER, reversed(chat_history)))
    return final_user_record.msg

  async def fetch_documents(self, url:str):
    # Fetching documents
    self.logger.info(f'Fetching URL "{url}"')
    docs = []
    loader = RecursiveUrlMultimediaLoader(
      url=url,
      max_depth=1,
      prevent_outside=False,
      use_async = True,
      cache_proxy_url = os.environ.get('HTTP_CACHE_PROXY', None)
    ) 
    docs = await loader.async_load()

    self.logger.info(f'Fetched {len(docs)} documents.')
    return docs
    
  async def construct_document_store(self, docs: [Document]):
    document_store = DocumentStore()
    await document_store.from_documents(docs)
    return document_store

  async def process(self, urls: Iterable, chat_history: [ChatRecord]) -> Generator[str, None, None]:

    final_user_input = self.get_final_user_input(chat_history)

    document_store = self.document_store
    docs = None
    try:
      if document_store == None:
        docs = await asyncio.gather(*[self.fetch_documents(url) for url in urls])
        docs = [doc for sub_docs in docs for doc in sub_docs]
        document_store = await self.construct_document_store(docs)
    except HTTPError as e:
      await asyncio.sleep(2) # To prevent SSE error of web page.
      yield f'獲取文件時發生錯誤 {str(e)}，請確認所提供文件存在且可被公開存取。'
      return
    
    task = ''
    if final_user_input == None:
      question = '時間、地點、目的、結論、摘要'
      llm_question = None
      task = 'summary'
      await asyncio.sleep(2) # To prevent SSE error of web page.
      yield '以下是這篇文章的摘要：\n'
    else:
      question = final_user_input
      llm_question = question
      task = 'qa'

    # Shortcut
    if docs != None:
      related_docs = docs
      modified_chat_history = self.replace_chat_history(chat_history, task, llm_question, related_docs)

    if docs == None or self.llm.is_too_long(modified_chat_history):
      # Retrieve
      related_docs = copy.deepcopy(await document_store.retrieve(question))
      while True:
        modified_chat_history = self.replace_chat_history(chat_history, task, llm_question, related_docs)
        if not self.llm.is_too_long(modified_chat_history): break
        related_docs = related_docs[:-1]

    # Free the unused VRAM
    del document_store
    gc.collect()
    torch.cuda.empty_cache()

    # Generate
    llm_input = self.generate_llm_input(task, llm_question, related_docs)
    self.logger.info('LLM input: {}'.format(llm_input))
    result = await self.llm.complete(modified_chat_history)

    # Egress filter
    is_english = self.is_english(result)
    self.logger.info(f'Is English: {is_english}')
    if task == 'summary' and is_english:
      result = await self.llm.complete([
        ChatRecord(
          role=Role.USER,
          msg=self.generate_llm_input('translate', result, [])
          ),
      ])

    # Log the QA history
    with jsonlines.open(self.log_path, mode='a', flush=True) as log:
      log.write({
        'time': time.time(),
        'url': urls,
        'question': question,
        'related_docs': [doc.page_content for doc in related_docs],
        'response': result
      })

    yield result