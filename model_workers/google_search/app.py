#!/bin/python3
# -#- coding: UTF-8 -*-

from worker_framework.datatype import ChatRecord, Role
from worker_framework.interfaces import GeneralProcessInterface
from typing import Generator


import logging
import asyncio
import os
import functools
import requests
import urllib.parse

class NoSearchEngineException(Exception):
  def __str__(self):
    return "外部搜尋無法連上。"

class GoogleSearchProcess(GeneralProcessInterface):
  def __init__(self, num_result = 5, search_engine_links=[]):
    self.logger = logging.getLogger(__name__)
    self.num_result = num_result
    self.search_engine_links = search_engine_links

  async def search(self, query:str) -> [dict[str, str]]:
    """
    Get first URL from the search result.
    """

    api_key = os.environ['GOOGLE_API_KEY']
    searching_engine_id = os.environ['GOOGLE_CSE_ID']
    restricted_sites = os.environ.get('SEARCH_RESTRICTED_SITES', '')
    blocked_sites = os.environ.get('SEARCH_BLOCKED_SITES', '')

    process_site_list = lambda x: list(filter(None, x.split(';')))
    restricted_sites = process_site_list(restricted_sites)
    blocked_sites = process_site_list(blocked_sites)
    
    query += ''.join([f' site:{s.strip()}' for s in restricted_sites])
    query += ''.join([f' -site:{s.strip()}' for s in blocked_sites])
    
    logging.debug(f'Restricted sites: {restricted_sites}')
    logging.debug(f'Blocked sites: {blocked_sites}')
    logging.debug(f'Query: {query}')

    endpoint = 'https://customsearch.googleapis.com/customsearch/v1'
    params = {
      'key': api_key,
      'cx': searching_engine_id,
      'q': query
    }

    results = []

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
      
      results = [
        {
          'link': item['link'],
          'title': item['title'],
          'snippet': item['snippet']
        }
        for item in resp['items']
      ]
      results = results[:min(len(results), self.num_result)]
    
    except Exception as e:
      self.logger.exception('Error while getting URLs for Google searching API')
    
    finally:
      return results

  async def process(self, chat_history: [ChatRecord]) -> Generator[str, None, None]:

    try:
    
      await asyncio.sleep(2) # To prevent SSE error of web page.
      
      latest_user_record = next(filter(lambda x: x.role == Role.USER, reversed(chat_history)))
      latest_user_msg = latest_user_record.msg
      query = latest_user_msg.strip()

      results = await self.search(query)

      if len(results) == 0: raise NoSearchEngineException

      yield f'### Google 搜尋結果  \n'
      for i, result in enumerate(results):
        url = result['link'].strip()
        title = result['title'].strip()
        snippet = result['snippet'].strip()
        yield f'{i+1}. [{title.strip()}]({url})  \n{snippet}  \n<br>\n'
      
      escaped_query = urllib.parse.quote_plus(query)

      yield f'  \n### 外部搜尋引擎連結  \n'
      for link in self.search_engine_links:
        link = link.replace('{}', escaped_query)
        yield f'- {link}  \n'
    
    except NoSearchEngineException as e:
      yield '外部搜尋暫無法連上，請稍後重試或是聯絡管理員。'

    except Exception as e:
      self.logger.exception('Unexpected error')
      yield '發生錯誤，請再試一次或是聯絡管理員。'