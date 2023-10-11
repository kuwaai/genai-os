#!/bin/python3
# -#- coding: UTF-8 -*-

from worker_framework.datatype import ChatRecord, Role
from worker_framework.interfaces import GeneralProcessInterface
from typing import Generator
from docqa.docqa import DocumentQa

import re
import logging
import asyncio

class NoUrlException(Exception):
  def __str__(self):
    return "找不到URL。"

class DocumentQaProcess(GeneralProcessInterface):
  def __init__(self):
    self.logger = logging.getLogger(__name__)
    self.app = DocumentQa()

  def extract_last_url(self, chat_history: [ChatRecord]):
    """
    Find the latest URL provided by the user and trim the chat history to there.
    """

    url = None
    begin_index = 0
    for i, record in enumerate(reversed(chat_history)):
      if record.role != Role.USER: continue
      urls_in_msg = re.findall(r'^(https?://[^\s]+)$', record.msg)
      if len(urls_in_msg) != 0: 
        url = urls_in_msg[-1]
        begin_index = len(chat_history) - i - 1
        break

    return url, chat_history[begin_index:]

  async def process(self, chat_history: [ChatRecord]) -> Generator[str, None, None]:

    url = None
    try:
    
      url, chat_history = self.extract_last_url(chat_history)
      if url == None : raise NoUrlException
      
      chat_history = [ChatRecord(msg=None, role=Role.USER)] + chat_history[1:]
      async for reply in self.app.process(url, chat_history):
        yield reply

    except NoUrlException as e:
      asyncio.sleep(2) # To prevent SSE error of web page.
      yield str(e)

    except Exception as e:
      asyncio.sleep(2) # To prevent SSE error of web page.
      self.logger.exception('Unexpected error')
      yield '發生錯誤，請再試一次或是聯絡管理員。'
