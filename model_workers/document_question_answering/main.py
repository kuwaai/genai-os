#!/bin/python3
# -#- coding: UTF-8 -*-

from worker_framework.datatype import ChatRecord, Role
from worker_framework.interfaces import GeneralProcessInterface
from typing import Generator
from recursive_url_multimedia_loader import RecursiveUrlMultimediaLoader
from document_store import DocumentStore
from taide_llm import TaideLlm
from pathlib import Path

import re
import logging
import chevron

class NoUrlException(Exception):
    pass

class DocumentQaProcess(GeneralProcessInterface):
  def __init__(self):
    self.logger = logging.getLogger(__name__)
    self.llm = TaideLlm()
    self.prefix = '/url'
    self.usage = '請輸入指令 "{} URL" 設定要針對哪份文件進行問答。\n再輸入一次該指令可重新指定文件。'.format(self.prefix)
  
  def extract_last_url(self, chat_history: [ChatRecord]):
    """
    Find the latest URL provided by the user and trim the chat history to there.
    """

    url = ''
    begin_index = 0
    for i, record in enumerate(reversed(chat_history)):
      if record.role != Role.USER: continue
      urls_in_msg = re.findall(r'^' + re.escape(self.prefix) + r'\s+(https?://[^\s]+)$', record.msg)
      if len(urls_in_msg) != 0: 
        url = urls_in_msg[-1]
        begin_index = len(chat_history) - i - 1
        break

    return url, chat_history[begin_index:]

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

  async def process(self, chat_history: [ChatRecord]) -> Generator[str, None, None]:

    try:
    
      # Extract context
      url, trimmed_history = self.extract_last_url(chat_history)
      chat_history = trimmed_history
      final_user_input = next(filter(lambda x: x.role == Role.USER, reversed(chat_history))) 

      if(url == ''): raise NoUrlException

      # Fetching documents
      self.logger.info('Fetching URL "{}"'.format(url))
      loader = RecursiveUrlMultimediaLoader(
        url=url,
        max_depth=1,
        prevent_outside=False,
        use_async = True,
        cache_proxy_url = 'http://web_cache:10250'
      ) 
      docs = await loader.async_load()
      self.logger.info('Fetched {} documents.'.format(len(docs)))
      
      document_store = DocumentStore()
      await document_store.from_documents(docs)
      
      task = ''
      if len(chat_history) == 1:
        question = '時間、地點、目的、結論、摘要'
        llm_question = None
        task = 'summary'
        yield '以下是這篇文章的摘要：\n'
      else:
        question = final_user_input.msg
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
    
    except NoUrlException:
      yield self.usage

    except Exception as e:
      self.logger.exception('Unexpected error')
      yield '發生錯誤，請再試一次。'
