#!/bin/python3
# -#- coding: UTF-8 -*-

from model_api_server.datatype import ChatRecord, Role
from model_api_server.interfaces import GeneralProcessInterface
from typing import Generator
from recursive_url_multimedia_loader import RecursiveUrlMultimediaLoader
from document_store import DocumentStore
from taide_llm import TaideLlm
from pathlib import Path

import re
import logging
import chevron

def extract_last_url(chat_history: [ChatRecord], prefix='/url'):
    """
    Find the latest URL provided by the user and trim the chat history to there.
    """

    url = ''
    begin_index = 0
    for i, record in enumerate(reversed(chat_history)):
      if record.role != Role.USER: continue
      urls_in_msg = re.findall(r'^' + re.escape(prefix) + r'\s+(https?://[^\s]+)$', record.msg)
      if len(urls_in_msg) != 0: 
        url = urls_in_msg[-1]
        begin_index = len(chat_history) - i - 1
        break

    return url, chat_history[begin_index:]

class NoUrlException(Exception):
    pass

class DocumentQaProcess(GeneralProcessInterface):
  def __init__(self):
    self.logger = logging.getLogger(__name__)
    self.llm = TaideLlm()
    self.prefix = '/url'
    self.usage = '請輸入指令 "{} URL" 設定要針對哪份文件進行問答。\n再輸入一次該指令可重新指定文件。'.format(self.prefix)

  async def process(self, chat_history: [ChatRecord]) -> Generator[str, None, None]:

    try:
    
      # Extract context
      url, trimmed_history = extract_last_url(chat_history, self.prefix)
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
      document_store.from_documents(docs)
      
      if len(chat_history) == 1:
        question = '時間、地點、目的、結論、摘要、總結'
        llm_question = '請提供這篇文章的要點概述。'
        yield '以下是這篇文章的摘要：\n'
      else:
        question = final_user_input.msg
        llm_question = question

      # Retrieve
      related_docs = document_store.retrieve(question)

      # Generation
      context_template = Path('prompt_template/context.mustache').read_text()
      context = chevron.render(context_template, {
        'docs': related_docs,
        'question': llm_question
      })
      self.logger.info('Context: {}'.format(context))
      modified_chat_history = chat_history[:-1] + [ChatRecord(context, Role.USER)]
      result = await self.llm.complete(
        modified_chat_history,
        system_prompt='You are a helpful assistant. 你是一個樂於助人的助手。'
      )
      yield result
    
    except NoUrlException:
      yield self.usage

    except Exception as e:
      self.logger.error(str(e))
      yield "發生錯誤，請再試一次。"
