#!/bin/python3
# -#- coding: UTF-8 -*-

from worker_framework.datatype import ChatRecord, Role
from typing import Generator
from pathlib import Path
from urllib.error import HTTPError

from .recursive_url_multimedia_loader import RecursiveUrlMultimediaLoader
from .document_store import DocumentStore
from .taide_llm import TaideLlm

import re
import logging
import chevron
import asyncio

class DocumentQa:
  def __init__(self):
    self.logger = logging.getLogger(__name__)
    self.llm = TaideLlm()
  
  def generate_llm_input(self, task, question, related_docs):
    
    template_path = 'prompt_template/' + f'llm_input_{task}.mustache'
    llm_input_template = Path(template_path).read_text()
    llm_input = chevron.render(llm_input_template, {
      'docs': related_docs,
      'question': question
    })

    return llm_input

  def replace_chat_history(self, chat_history, task, question, related_docs):
    llm_input = self.generate_llm_input(task, question, related_docs)
    modified_chat_history = chat_history[:-1] + [ChatRecord(llm_input, Role.USER)]

    return modified_chat_history
  
  def is_english(self, paragraph:str, threshold=0.8):
    total_count = len(paragraph)
    english_charter_count = len(paragraph.encode("ascii", "ignore"))

    return english_charter_count / total_count >= threshold

  def get_final_user_input(self, chat_history: [ChatRecord]) -> str:
    final_user_record = next(filter(lambda x: x.role == Role.USER, reversed(chat_history)))
    return final_user_record.msg

  async def process(self, url: str, chat_history: [ChatRecord]) -> Generator[str, None, None]:

    final_user_input = self.get_final_user_input(chat_history)

    # Fetching documents
    self.logger.info(f'Fetching URL "{url}"')
    try:
      loader = RecursiveUrlMultimediaLoader(
        url=url,
        max_depth=1,
        prevent_outside=False,
        use_async = True,
        cache_proxy_url = 'http://web_cache:10250'
      ) 
      docs = await loader.async_load()
    except HTTPError as e:
      asyncio.sleep(2) # To prevent SSE error of web page.
      yield f'獲取文件時發生錯誤 {str(e)}，請確認所提供文件存在且可被公開存取。'
      return

    self.logger.info(f'Fetched {len(docs)} documents.')
    
    document_store = DocumentStore()
    await document_store.from_documents(docs)
    
    task = ''
    if final_user_input == None:
      question = '時間、地點、目的、結論、摘要'
      llm_question = None
      task = 'summary'
      asyncio.sleep(2) # To prevent SSE error of web page.
      yield '以下是這篇文章的摘要：\n'
    else:
      question = final_user_input
      llm_question = question
      task = 'qa'

    # Shortcut
    related_docs = docs
    modified_chat_history = self.replace_chat_history(chat_history, task, llm_question, related_docs)

    if self.llm.is_too_long(modified_chat_history):
      # Retrieve
      related_docs = await document_store.retrieve(question)
      modified_chat_history = self.replace_chat_history(chat_history, task, llm_question, related_docs)

    # Generate
    llm_input = self.generate_llm_input(task, llm_question, related_docs)
    self.logger.info('LLM input: {}'.format(llm_input))
    result = await self.llm.complete(modified_chat_history)

    # Egress filter
    is_english = self.is_english(result)
    self.logger.info(f'Is English: {is_english}')
    if is_english:
      result = await self.llm.complete([
        ChatRecord(
          role=Role.USER,
          msg=self.generate_llm_input('translate', result, [])
          ),
      ])

    yield result