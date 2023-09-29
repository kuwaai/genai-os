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
    self.llm = TaideLlm()

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
    
    # Summarize question
    question = final_user_input.msg
#     question = await self.llm.complete(
#       chat_history,
#       system_prompt="""
# 請根據談話內容，改寫使用者的問題。
# """
#     )

    # Retrieve
    related_docs = document_store.retrieve(question)
    
    # Generation
    context_template = Path('prompt_template/context.mustache').read_text()
    context = chevron.render(context_template, {
      'docs': related_docs,
      'question': question
    })
    self.logger.info('Context: {}'.format(context))
    modified_chat_history = chat_history[:-1] + [ChatRecord(context, Role.USER)]
    result = await self.llm.complete(
      modified_chat_history,
      system_prompt="你的名子是 TAIDE, 你是個能夠理解使用者的大語言模型AI，能流暢的以繁體中文溝通，能專業且流利的回答使用者，專長在文本翻譯、寫文章、寫信、自動摘要上"
      # system_prompt="""
# 你是一個有幫助且精準的大語言模型。
# 請使用繁體中文，根據所提供的資料回答使用者的問題。
# 若資料內容無法回答使用者問題就回答不知道。
# """
    )
    yield result
