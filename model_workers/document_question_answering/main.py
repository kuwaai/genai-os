#!/bin/python3
# -#- coding: UTF-8 -*-

from model_api_server.datatype import ChatRecord, Role
from model_api_server.interfaces import GeneralProcessInterface
from typing import Generator
from recursive_url_multimedia_loader import RecursiveUrlMultimediaLoader
import re

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
    pass

  async def process(self, chat_history: [ChatRecord]) -> Generator[str, None, None]:
    
    final_user_input = next(filter(lambda x: x.role == Role.USER, reversed(chat_history)))
    
    url = extract_last_url(chat_history)

    loader = RecursiveUrlMultimediaLoader(
        url=url,
        max_depth=2,
        prevent_outside=False,
        use_async = True
    ) 
    docs = await loader.async_load()
    
    for doc in docs[:10]:
      yield str(doc)