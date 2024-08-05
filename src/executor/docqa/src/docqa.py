#!/bin/python3
# -#- coding: UTF-8 -*-

from typing import Generator, Iterable
from collections import namedtuple
from pathlib import Path
from urllib.error import HTTPError
from functools import lru_cache
from langchain.docstore.document import Document
from kuwa.executor import Modelfile
from kuwa.client import KuwaClient

from .recursive_url_multimedia_loader import RecursiveUrlMultimediaLoader
from .document_store import DocumentStore

import i18n
import re
import gc
import os
import logging
import chevron
import asyncio
import copy
import time
import pathlib

logger = logging.getLogger(__name__)

class DocQa:

  def __init__(
    self,
    document_store:DocumentStore = DocumentStore,
    vector_db:str = None,
    vector_db_ttl_sec:int = 600,
    llm:KuwaClient = KuwaClient(),
    lang:str="en",
    with_ref:bool=False,
    display_ref_content:bool=True,
    user_agent:str = None
    ):
    self.llm = llm
    self.lang = lang
    self.with_ref = with_ref
    self.display_ref_content = display_ref_content
    self.user_agent = user_agent
    self.vector_db_ttl_sec = vector_db_ttl_sec
    if vector_db != None:
      self.pre_build_db = True
      self.document_store = DocumentStore.load(vector_db)
    else:
      self.pre_build_db = False
      self.document_store:DocumentStore = document_store
  
  def generate_llm_input(self, task, question, related_docs, override_prompt:str=None):
    
    docs = [dict(title=doc.metadata.get("title"), **dict(doc)) for doc in related_docs]
    template_path = f'lang/{self.lang}/prompt_template/llm_input_{task}.mustache'
    llm_input_template = Path(template_path).read_text(encoding="utf8")
    llm_input = chevron.render(llm_input_template, {
      'docs': docs,
      'question': question,
      'override_prompt': override_prompt
    })

    return llm_input

  def replace_chat_history(self, chat_history:[dict], task:str, question:str, related_docs:[str], override_prompt:str):
    llm_input = self.generate_llm_input(task, question, related_docs, override_prompt)
    modified_chat_history = chat_history[:-1] + [{"role": "user", "content": llm_input}]
    if modified_chat_history[0]["content"] is None:
      if len(modified_chat_history) != 2: # Multi-round
        modified_chat_history[0]["content"] = i18n.t("docqa.summary_prompt")
      else: # Single-round
        modified_chat_history = modified_chat_history[1:]
    modified_chat_history = [
      {"content": "[Empty message]", "role": r["role"]} if r["content"] == '' else r
      for r in modified_chat_history
    ]

    return modified_chat_history
  
  def is_english(self, paragraph:str, threshold=0.8):
    total_count = len(paragraph)
    english_charter_count = len(paragraph.encode("ascii", "ignore"))
    english_rate = 0 if total_count == 0 else english_charter_count / total_count

    return english_rate >= threshold

  def get_final_user_input(self, chat_history: [dict]) -> str:
    final_user_record = next(filter(lambda x: x['role'] == "user", reversed(chat_history)))
    return final_user_record['content']

  async def fetch_documents(self, url:str):
    # Fetching documents
    logger.info(f'Fetching URL "{url}"')
    docs = []
    loader = RecursiveUrlMultimediaLoader(
      url=url,
      max_depth=1,
      prevent_outside=False,
      use_async = True,
      cache_proxy_url = os.environ.get('HTTP_CACHE_PROXY', None),
      forge_user_agent=self.user_agent
    ) 
    try:
      docs = await loader.async_load()
      logger.info(f'Fetched {len(docs)} documents.')
    except Exception as e:
      logger.warning(str(e))
      docs = []
    finally:
      return docs
  
  async def construct_document_store(self, urls: [str], ttl_sec=600):
    """
    To build a vector database by extracting information from given URLs, first
    check if a cached version is available. If a cached version exists and
    hasn't exceeded the specified time to live (ttl_sec), utilize it; otherwise,
    fetch and cache the data from the provided URLs before constructing the
    final vector database.
    
    Parameters:
      urls: The URLs to fetch documents.
      ttl_sec: The time to live of the cached documents in unit of seconds. 
    
    Return:
      The object of constructed vector database. Raise exception if no document was fetched.
    """
  
    urls = frozenset(urls)
    ttl_hash = time.time() // ttl_sec

    logger.debug(f"Parameter to cache: {urls}, {ttl_hash}")

    result = await self._cached_construct_document_store(urls, ttl_hash)
    logger.debug(f"Cache info: {self._cached_construct_document_store.cache_info()}")
    return result

  @lru_cache()
  async def _cached_construct_document_store(self, urls:frozenset, ttl_hash:int=None):
    
    if len(urls) == 0: return None
    
    docs = await asyncio.gather(*[self.fetch_documents(url) for url in urls])
    docs = [doc for sub_docs in docs for doc in sub_docs]
    if len(docs) == 0:
      raise RuntimeError("Error fetching documents.")

    document_store = copy.deepcopy(self.document_store)
    await document_store.from_documents(docs)
    return document_store, docs

  def filter_detail(self, msg):
    if msg is None: return None
    pattern = r"<details>.*</details>"
    return re.sub(pattern=pattern, repl='', string=msg, flags=re.DOTALL)

  def format_references(self, docs:[Document]):
    Reference = namedtuple("Reference", "source, title, content")
    refs = [
      Reference(
        source=doc.metadata.get("source"),
        title=doc.metadata.get("title", doc.metadata.get("filename")),
        content=doc.page_content,
      ) for doc in docs
    ]
    refs = filter(lambda x: x.source, refs)
    result = f"\n\n<details><summary>{i18n.t('docqa.reference')}</summary>\n\n"
    for i, ref in enumerate(refs):
      
      src = ref.source
      title = ref.title if ref.title is not None else src
      content = f'\n\n```plaintext\n{ref.content}\n```\n' if self.display_ref_content else ''
      link = src if src.startswith("http") else pathlib.Path(src).as_uri()
      result += f'{i+1}. [{title}]({link}){content}\n'
    result += f"</details>"

    return result

  async def process(self, urls: Iterable, chat_history: [dict], modelfile:Modelfile, auth_token=None) -> Generator[str, None, None]:
    override_qa_prompt = modelfile.override_system_prompt
    chat_history = [{"content": self.filter_detail(i["content"]), "role": i["role"]} for i in chat_history]

    logger.debug(f"Chat history: {chat_history}")

    final_user_input = self.get_final_user_input(chat_history)
    if final_user_input is not None:
      final_user_input = "{before}{user}{after}".format(
        before = modelfile.before_prompt,
        user = final_user_input,
        after = modelfile.after_prompt
      )

    document_store = self.document_store
    docs = None
    if not self.pre_build_db:
      try:
        document_store, docs = await self.construct_document_store(urls, ttl_sec=self.vector_db_ttl_sec)
      except Exception:
        await asyncio.sleep(2) # To prevent SSE error of web page.
        yield i18n.t('docqa.error_fetching_document')
        return
    
    task = ''
    if final_user_input == "":
      question = i18n.t("docqa.summary_question") 
      llm_question = None
      task = 'summary'
      await asyncio.sleep(2) # To prevent SSE error of web page.
      yield i18n.t("docqa.summary_prefix")+'\n'
    else:
      question = final_user_input
      llm_question = question
      task = 'qa'

    # Shortcut
    if docs != None:
      related_docs = docs
      modified_chat_history = self.replace_chat_history(chat_history, task, llm_question, related_docs, override_prompt=override_qa_prompt)

    if docs == None or self.llm.is_too_long(modified_chat_history):
      # Retrieve
      related_docs = copy.deepcopy(await document_store.retrieve(question))
      logger.info("Related documents: {}".format(related_docs))
      # [TODO] the related-document will be cleared when the history is too long
      while True:
        modified_chat_history = self.replace_chat_history(chat_history, task, llm_question, related_docs, override_prompt=override_qa_prompt)
        if not self.llm.is_too_long(modified_chat_history) or len(related_docs)==0: break
        related_docs = related_docs[:-1]
        logger.info("Prompt length exceeded the permitted limit, necessitating truncation.")

    # Free the unused VRAM
    del document_store
    gc.collect()

    # Generate
    llm_input = self.generate_llm_input(task, llm_question, related_docs, override_prompt=override_qa_prompt)
    logger.info("Related documents: {}".format(related_docs))
    logger.info('LLM input: {}'.format(llm_input))
    # result = ''
    generator = self.llm.chat_complete(
      auth_token=auth_token,
      messages=modified_chat_history
    )
    async for chunk in generator:
      yield chunk

    if self.with_ref and len(related_docs)!=0:
      yield self.format_references(related_docs)

    # Egress filter
    # is_english = self.is_english(result)
    # logger.info(f'Is English: {is_english}')
    # if task == 'summary' and is_english:
    #   result = await self.llm.chat_complete(
    #     auth_token=auth_token,
    #     messages=[
    #       {
    #         "role": "user",
    #         "content": self.generate_llm_input('translate', result, [])
    #       },
    #     ]
    #   )

    # yield result