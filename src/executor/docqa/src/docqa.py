#!/bin/python3
# -#- coding: UTF-8 -*-

from typing import Generator, Iterable
from collections import namedtuple
from pathlib import Path
from urllib.error import HTTPError
from langchain.docstore.document import Document
from kuwa.executor import Modelfile

from .recursive_url_multimedia_loader import RecursiveUrlMultimediaLoader
from .document_store import DocumentStore
from .kuwa_llm_client import KuwaLlmClient

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

class DocQa:

  def __init__(
    self,
    document_store:DocumentStore = DocumentStore,
    vector_db:str = None,
    llm:KuwaLlmClient = KuwaLlmClient(),
    lang:str="en",
    with_ref:bool=False,
    user_agent:str = None
    ):
    self.logger = logging.getLogger(__name__)
    self.llm = llm
    self.lang = lang
    self.with_ref = with_ref
    self.user_agent = user_agent
    if vector_db != None:
      self.pre_build_db = True
      self.document_store = DocumentStore.load(vector_db)
    else:
      self.pre_build_db = False
      self.document_store:DocumentStore = document_store
  
  def generate_llm_input(self, task, question, related_docs, override_prompt:str=None):
    
    template_path = f'lang/{self.lang}/prompt_template/llm_input_{task}.mustache'
    llm_input_template = Path(template_path).read_text(encoding="utf8")
    llm_input = chevron.render(llm_input_template, {
      'docs': related_docs,
      'question': question,
      'override_prompt': override_prompt
    })

    return llm_input

  def replace_chat_history(self, chat_history:[dict], task:str, question:str, related_docs:[str], override_prompt:str):
    llm_input = self.generate_llm_input(task, question, related_docs, override_prompt)
    modified_chat_history = chat_history[:-1] + [{"isbot": False, "msg": llm_input}]
    if modified_chat_history[0]["msg"] is None:
      if len(modified_chat_history) != 2: # Multi-round
        modified_chat_history[0]["msg"] = i18n.t("docqa.summary_prompt")
      else: # Single-round
        modified_chat_history = modified_chat_history[1:]
    modified_chat_history = [
      {"msg": "[Empty message]", "isbot": r["isbot"]} if r["msg"] == '' else r
      for r in modified_chat_history
    ]

    return modified_chat_history
  
  def is_english(self, paragraph:str, threshold=0.8):
    total_count = len(paragraph)
    english_charter_count = len(paragraph.encode("ascii", "ignore"))
    english_rate = 0 if total_count == 0 else english_charter_count / total_count

    return english_rate >= threshold

  def get_final_user_input(self, chat_history: [dict]) -> str:
    final_user_record = next(filter(lambda x: x['isbot'] == False, reversed(chat_history)))
    return final_user_record['msg']

  async def fetch_documents(self, url:str):
    # Fetching documents
    self.logger.info(f'Fetching URL "{url}"')
    docs = []
    loader = RecursiveUrlMultimediaLoader(
      url=url,
      max_depth=1,
      prevent_outside=False,
      use_async = True,
      cache_proxy_url = os.environ.get('HTTP_CACHE_PROXY', None),
      forge_user_agent=self.user_agent
    ) 
    docs = await loader.async_load()

    self.logger.info(f'Fetched {len(docs)} documents.')
    return docs
    
  async def construct_document_store(self, docs: [Document]):
    document_store = self.document_store
    await document_store.from_documents(docs)
    return document_store

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
      content = ref.content
      link = src if src.startswith("http") else pathlib.Path(src).as_uri()
      result += f'{i+1}. [{title}]({link})\n\n```plaintext\n{content}\n```\n\n'
    result += f"</details>"

    return result

  async def process(self, urls: Iterable, chat_history: [dict], modelfile:Modelfile, auth_token=None) -> Generator[str, None, None]:
    override_qa_prompt = modelfile.override_system_prompt
    chat_history = [{"msg": i["content"], "isbot": i["role"]=="assistant"} for i in chat_history]
    chat_history = [{"msg": self.filter_detail(i["msg"]), "isbot": i["isbot"]} for i in chat_history]

    self.logger.debug(f"Chat history: {chat_history}")

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
      if len(urls) == 1:
        try:
          docs = await self.fetch_documents(urls[0])
        except HTTPError as e:
          await asyncio.sleep(2) # To prevent SSE error of web page.
          yield i18n.t('docqa.error_fetching_document').format(str(e))
          return
      else:
        docs = await asyncio.gather(*[self.fetch_documents(url) for url in urls])
        docs = [doc for sub_docs in docs for doc in sub_docs]
      document_store = await self.construct_document_store(docs)

    
    task = ''
    if final_user_input is None:
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
      self.logger.info("Related documents: {}".format(related_docs))
      # [TODO] the related-document will be cleared when the history is too long
      while True:
        modified_chat_history = self.replace_chat_history(chat_history, task, llm_question, related_docs, override_prompt=override_qa_prompt)
        if not self.llm.is_too_long(modified_chat_history) or len(related_docs)==0: break
        related_docs = related_docs[:-1]
        self.logger.info("Prompt length exceeded the permitted limit, necessitating truncation.")

    # Free the unused VRAM
    del document_store
    gc.collect()

    # Generate
    llm_input = self.generate_llm_input(task, llm_question, related_docs, override_prompt=override_qa_prompt)
    self.logger.info("Related documents: {}".format(related_docs))
    self.logger.info('LLM input: {}'.format(llm_input))
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
    # self.logger.info(f'Is English: {is_english}')
    # if task == 'summary' and is_english:
    #   result = await self.llm.chat_complete(
    #     auth_token=auth_token,
    #     messages=[
    #       {
    #         "isbot": False,
    #         "msg": self.generate_llm_input('translate', result, [])
    #       },
    #     ]
    #   )

    # yield result