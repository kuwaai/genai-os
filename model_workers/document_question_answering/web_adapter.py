#!/bin/python3
# -#- coding: UTF-8 -*-

from worker_framework.datatype import ChatRecord, Role
from worker_framework.interfaces import GeneralProcessInterface
from typing import Generator
from docqa.docqa import DocumentQa

import re
import logging
import asyncio
import os
import functools
import itertools
import requests

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
      async for reply in self.app.process([url], chat_history):
        yield reply

    except NoUrlException as e:
      await asyncio.sleep(2) # To prevent SSE error of web page.
      yield str(e)

    except Exception as e:
      await asyncio.sleep(2) # To prevent SSE error of web page.
      self.logger.exception('Unexpected error')
      yield '發生錯誤，請再試一次或是聯絡管理員。'

class DatabaseQaProcess(GeneralProcessInterface):
  def __init__(self, database_path=''):
    self.logger = logging.getLogger(__name__)
    self.database_path = database_path
    self.app = DocumentQa(self.database_path)

  async def process(self, chat_history: [ChatRecord]) -> Generator[str, None, None]:

    try:
      
      if len(chat_history) > 0 and chat_history[-1].msg == '/reload':
        self.app = DocumentQa(self.database_path)
        await asyncio.sleep(2) # To prevent SSE error of web page.
        yield '資料庫已重新載入。'
        return

      async for reply in self.app.process(None, chat_history):
        yield reply

    except Exception as e:
      await asyncio.sleep(2) # To prevent SSE error of web page.
      self.logger.exception('Unexpected error')
      yield '發生錯誤，請再試一次或是聯絡管理員。'

class SearchQaProcess(GeneralProcessInterface):
  def __init__(self):
    self.logger = logging.getLogger(__name__)
    self.app = DocumentQa()

  async def is_url_reachable(self, url:str, timeout=5) -> bool:
    loop = asyncio.get_running_loop()
    resp = None
    try:
      resp = await loop.run_in_executor(
        None,
        functools.partial(
          requests.get,
          url,
          timeout=timeout
        )
      )
    except Exception as e:
      self.logger.exception(e)
      pass
    finally:
      return resp != None and resp.ok

  async def search_url(self, chat_history: [ChatRecord], num_url = 3) -> str:
    """
    Get first URL from the search result.
    """

    api_key = os.environ['GOOGLE_API_KEY']
    searching_engine_id = os.environ['GOOGLE_CSE_ID']
    latest_user_record = next(filter(lambda x: x.role == Role.USER, reversed(chat_history)))
    latest_user_msg = latest_user_record.msg
    
    endpoint = 'https://customsearch.googleapis.com/customsearch/v1'
    params = {
      'key': api_key,
      'cx': searching_engine_id,
      'q': latest_user_msg
    }

    urls = []

    try:

      loop = asyncio.get_running_loop()
      resp = await loop.run_in_executor(
        None,
        functools.partial(
          requests.get,
          endpoint,
          params = params
        )
      )
      

      self.logger.debug(f'Search response ({resp.status_code}):\n{resp.content}')

      if not resp.ok or 'error' in resp.json(): raise ValueError()
      resp  = resp.json()
      
      urls = [item['link'] for item in resp['items']]
      urls_reachable = await asyncio.gather(*[self.is_url_reachable(url) for url in urls])
      self.logger.debug(list(zip(urls, urls_reachable)))
      urls = list(itertools.compress(urls, urls_reachable))
      urls = urls[:min(len(urls), num_url)]
    
    except Exception as e:
      self.logger.exception('Error while getting URLs for Google searching API')
    
    finally:
      return urls, [latest_user_record]

  async def process(self, chat_history: [ChatRecord]) -> Generator[str, None, None]:

    try:
    
      urls, chat_history = await self.search_url(chat_history)

      if len(urls) == 0: raise NoUrlException
      
      async for reply in self.app.process(urls, chat_history):
        yield reply
      
      yield f'\n\n參考資料：\n'
      for url in urls:
        yield f'{url}\n'
    
    except NoUrlException as e:
      await asyncio.sleep(2) # To prevent SSE error of web page.
      yield '外部搜尋暫無法連上，請稍後重試或是聯絡管理員。'

    except Exception as e:
      await asyncio.sleep(2) # To prevent SSE error of web page.
      self.logger.exception('Unexpected error')
      yield '發生錯誤，請再試一次或是聯絡管理員。'