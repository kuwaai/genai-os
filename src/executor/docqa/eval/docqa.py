#!/bin/python3
# -#- coding: UTF-8 -*-

from typing import Generator, Iterable
from collections import namedtuple
from pathlib import Path
from urllib.error import HTTPError
from pprint import pformat
from langchain.docstore.document import Document
from kuwa.executor import Modelfile

from .recursive_url_multimedia_loader import RecursiveUrlMultimediaLoader
from .document_store import DocumentStore
from .kuwa_llm_client import KuwaLlmClient
from .eval import Eval

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
import json

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
    """
    This function generates a formatted input string suitable for the Kuwa LLM model based on the provided task, question, related documents, and an optional override prompt.

    Args:
        task (str): The type of task to be performed (e.g., "qa" for question answering, "summary" for summarization, and "translate").
        question (str): The user's question or prompt.
        related_docs (List[Document]): A list of Document objects containing relevant information for the task. These document came from the retreiever embedding.
        override_prompt (str, optional): An optional override prompt to use in place of the default prompt for the task. Defaults to None.

    Returns:
        str: The formatted LLM input string.
    """
    # Convert related documents to a dictionary format suitable for templating
    docs = [dict(title=doc.metadata.get("title"), **dict(doc)) for doc in related_docs]
    # Load the appropriate prompt template based on the task and language
    template_path = f'lang/{self.lang}/prompt_template/llm_input_{task}.mustache'
    llm_input_template = Path(template_path).read_text(encoding="utf8")
    # Render the template with the provided data
    llm_input = chevron.render(llm_input_template, {
      'docs': docs,
      'question': question,
      'override_prompt': override_prompt
    })
    return llm_input

  def replace_chat_history(self, chat_history:[dict], task:str, question:str, related_docs:[str], override_prompt:str):
    """
    This function modifies the provided chat history to include the generated LLM input for processing by the LLM model.

    Args:
        chat_history (List[Dict]): A list of dictionaries representing the chat history, where each dictionary contains keys like "msg" (message content) and "isbot" (boolean indicating if the message is from a bot).
        task (str): The type of task to be performed (e.g., "qa" for question answering, "summary" for summarization).
        question (str): The user's question or prompt.
        related_docs (List[str]): A list of URLs or file paths pointing to documents relevant to the user's query.
        override_prompt (str, optional): An optional override prompt to use in place of the default prompt for the task. Defaults to None.

    Returns:
        List[Dict]: The modified chat history with the LLM input message inserted.
    """
    # Generate the LLM input using the provided information
    llm_input = self.generate_llm_input(task, question, related_docs, override_prompt)
    # Modify the chat history to include the LLM input
    modified_chat_history = chat_history[:-1] + [{"isbot": False, "msg": llm_input}]
    # If the first message in the chat history is empty
    if modified_chat_history[0]["msg"] is None:
      # If it's a multi-round conversation, set the first message to a summary prompt
      if len(modified_chat_history) != 2: # Multi-round
        modified_chat_history[0]["msg"] = i18n.t("docqa.summary_prompt")
      # Otherwise, remove the first message
      else: # Single-round
        modified_chat_history = modified_chat_history[1:]
    # Replace empty messages with a placeholder
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
    """
    This function extracts the final user input (message) from the provided chat history.

    Args:
        chat_history (List[Dict]): A list of dictionaries representing the chat history, where each dictionary contains keys like "msg" (message content) and "isbot" (boolean indicating if the message is from a bot).

    Returns:
        str: The final user input message from the chat history, or None if no user messages are found.
    """
    final_user_record = next(filter(lambda x: x['isbot'] == False, reversed(chat_history)))
    return final_user_record['msg']

  async def fetch_documents(self, url:str):
    """
    This function asynchronously fetches documents from the provided URL using a RecursiveUrlMultimediaLoader.

    Args:
        url (str): The URL of the webpage or document to be fetched.

    Returns:
        List[Document]: A list of Document objects containing the fetched content, or an empty list if there were errors or no documents were found.
    """
    # Fetching documents
    self.logger.info(f'Fetching URL "{url}"')
    docs = []
    # Create a RecursiveUrlMultimediaLoader to handle fetching documents
    loader = RecursiveUrlMultimediaLoader(
      url=url,
      max_depth=1,
      prevent_outside=False,
      use_async = True,
      cache_proxy_url = os.environ.get('HTTP_CACHE_PROXY', None),
      forge_user_agent=self.user_agent
    ) 
    try:
      # Asynchronously load the documents using the loader
      docs = await loader.async_load()
      # Log the number of documents fetched
      self.logger.info(f'Fetched {len(docs)} documents.')
    except Exception as e:
      docs = []
    finally:
      return docs
    
  async def construct_document_store(self, docs: [Document]):
    document_store = self.document_store
    # Calls function from document_store to make the docs goes through embedding and forms vectorDB
    await document_store.from_documents(docs)
    return document_store

  def filter_detail(self, msg):
    """
    This function removes HTML details tags (<details>...</details>) from the provided message string.

    Args:
        msg (str): The message string potentially containing HTML details tags.

    Returns:
        str: The message string with HTML details tags removed. If the input message is None, it returns None.
    """
    if msg is None: return None
    pattern = r"<details>.*</details>"
    return re.sub(pattern=pattern, repl='', string=msg, flags=re.DOTALL)

  def format_references(self, docs:[Document]):
    """
    This function formats a list of Document objects into a user-readable reference list with snippets.

    Args:
        docs (List[Document]): A list of Document objects containing information about the reference sources.

    Returns:
        str: The formatted reference list as a string, including source links, titles, and snippets. If no documents have a valid source, it returns an empty string.
    """
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
    
    """
    This asynchronous function processes a conversation history and a list of URLs to answer questions or summarize documents in a conversational manner.

    It interacts with the Kuwa LLM model to generate responses based on the provided information.

    Args:
        urls (Iterable[str]): An iterable of URLs pointing to documents relevant to the user's query.
        chat_history (List[Dict]): A list of dictionaries representing the conversation history, where each dictionary contains keys like "msg" (message content) and "isbot" (boolean indicating if the message is from a bot).
        modelfile (Modelfile): A Modelfile object containing configuration parameters for the conversation.
        auth_token (str, optional): An optional authentication token for the LLM model. Defaults to None.

    Yields:
        str: Yields chunks of the conversation generated by the LLM model, including the answer or summary and potentially references to retrieved documents.

    Returns:
        None: The function itself does not return a value, it yields the conversation chunks asynchronously.
    """
    retriever_result = {"questions": []}
    override_qa_prompt = modelfile.override_system_prompt
    chat_history = [{"msg": i["content"], "isbot": i["role"]=="assistant"} for i in chat_history]
    chat_history = [{"msg": self.filter_detail(i["msg"]), "isbot": i["isbot"]} for i in chat_history]

    self.logger.debug(f"Chat history: {chat_history}")

    # final_user_input = self.get_final_user_input(chat_history)
    # if final_user_input is not None:
    #   final_user_input = "{before}{user}{after}".format(
    #     before = modelfile.before_prompt,
    #     user = final_user_input,
    #     after = modelfile.after_prompt
    #   )


    document_store = self.document_store
    docs = None
    if not self.pre_build_db:
      if len(urls) == 1:
        
        docs = await self.fetch_documents(urls[0])
        if len(docs) == 0:
          await asyncio.sleep(2) # To prevent SSE error of web page.
          yield i18n.t('docqa.error_fetching_document')
          return
      else:
        docs = await asyncio.gather(*[self.fetch_documents(url) for url in urls])
        docs = [doc for sub_docs in docs for doc in sub_docs]
      document_store = await self.construct_document_store(docs)

    text = docs[0] # the text will be coming from the document of what user upload
    data_sample = Eval.clean_llm_response(Eval.generate_questions_RAG(text))#evalotor.generate_question(text)
    data_sample['contexts'] = []
    data_sample['answer'] = []
    gen_questions = data_sample['questions']
    self.logger.info(f"{gen_questions}")
    for gen_question in gen_questions:
      retriever_result = {"questions": []}
      final_user_input = gen_question
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
        
        self.logger.info("Related documents: {}".format(related_docs))
        # [TODO] the related-document will be cleared when the history is too long
        while True:
          modified_chat_history = self.replace_chat_history(chat_history, task, llm_question, related_docs, override_prompt=override_qa_prompt)
          if not self.llm.is_too_long(modified_chat_history) or len(related_docs)==0: break
          related_docs = related_docs[:-1] 
          self.logger.info("Prompt length exceeded the permitted limit, necessitating truncation.")

      #result = Eval.eval_retriever(gen_question, related_docs)
      #retriever_result["questions"].append(result)
      #retriever_result = Eval.yield_retriever(retriever_result)
      #yield retriever_result

      page_content = Eval.extract_page_content(related_docs)
      data_sample['contexts'].append(page_content)
      answer = Eval.generate_answer(gen_question, page_content)
      data_sample['answer'].append(answer)
      yield pformat(data_sample)
      Eval.ragas_eval(data_sample)

      break
      #end2end_result = Eval.eval_end2end(gen_question, answer)
      #yield Eval.yield_end2end(end2end_result)
      # yield pformat(Eval.eval_end2end(gen_question, answer))

      # Free the unused VRAM
      # del document_store
      # gc.collect()

      # # # Generate
      # llm_input = self.generate_llm_input(task, llm_question, related_docs, override_prompt=override_qa_prompt)
      # self.logger.info("Related documents: {}".format(related_docs))
      # self.logger.info('LLM input: {}'.format(llm_input))
      # # result = ''
      # generator = self.llm.chat_complete(
      #   auth_token=auth_token,
      #   messages=modified_chat_history
      # )
      # buffer = ""
      # async for chunk in generator:
      #   buffer += chunk


      # self.logger.info("Buffer is: " + buffer)
      # result = evalotor.eval_end2end(gen_question, buffer)
      # self.logger.info("Return result is: " + result)
      # yield result
      #   yield chunk
      

      if self.with_ref and len(related_docs)!=0:
        yield self.format_references(related_docs)


      # # # Generate
      # llm_input = self.generate_llm_input(task, llm_question, related_docs, override_prompt=override_qa_prompt)
      # self.logger.info("Related documents: {}".format(related_docs))
      # self.logger.info('LLM input: {}'.format(llm_input))
      # generator = self.llm.chat_complete(
      #     # auth_token=auth_token,  # Optional
      #     # messages=modified_chat_history  # Optional (depending on your model)
      # )
      # async for chunk in generator:
      #   # Call your function that returns a JSON object
      #   json_data = await self.your_function(chunk)  # Replace with your function name
      #   # Convert the JSON object to a string
      #   json_string = json.dumps(json_data)
      #   yield json_string

          # if self.with_ref and len(related_docs)!=0:
      #   yield self.format_references(related_docs)

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