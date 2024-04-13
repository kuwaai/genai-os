#!/bin/python3
# -#- coding: UTF-8 -*-

import os
import re
import logging
import asyncio
import functools
import itertools
import requests
import json
import i18n

from typing import Generator
from kuwa.executor import LLMExecutor

from src.webqa import WebQa
from src.kuwa_llm_client import KuwaLlmClient
from src.document_store import DocumentStore

logger = logging.getLogger(__name__)

class NoUrlException(Exception):
    def __init__(self, msg):
        self.msg = msg
    def __str__(self):
        return msg

class WebQaExecutor(LLMExecutor):
    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        parser.add_argument('--lang', default="en", help='The language code to internationalize the aplication. See \'lang/\'')
        parser.add_argument('--api_base_url', default="http://127.0.0.1/", help='The API base URL of Kuwa multi-chat WebUI')
        parser.add_argument('--api_key', default=None, help='The API authentication token of Kuwa multi-chat WebUI')
        parser.add_argument('--limit', default=30720, type=int, help='The limit of the LLM\'s context window')
        parser.add_argument('--model', default="gemini-pro", help='The model name (access code) on Kuwa multi-chat WebUI')
        parser.add_argument('--mmr_k', default=6, type=int, help='Number of chunk to retrieve after Maximum Marginal Relevance (MMR).')
        parser.add_argument('--mmr_fetch_k', default=12, type=int, help='Number of chunk to retrieve before Maximum Marginal Relevance (MMR).')
        parser.add_argument('--chunk_size', default=512, type=int, help='The charters in the chunk.')
        parser.add_argument('--chunk_overlap', default=128, type=int, help='The overlaps between chunks.')

    def setup(self):
        i18n.load_path.append(f'lang/{self.args.lang}/')
        i18n.config.set("error_on_missing_translation", True)
        i18n.config.set("locale", self.args.lang)

        self.llm = KuwaLlmClient(
            base_url = self.args.api_base_url,
            model=self.args.model,
            auth_token=self.args.api_key
        )
        self.document_store = DocumentStore(
            mmr_k = self.args.mmr_k,
            mmr_fetch_k = self.args.mmr_fetch_k,
            chunk_size = self.args.chunk_size,
            chunk_overlap = self.args.chunk_overlap
        )
        self.webqa = WebQa(
            document_store = self.document_store,
            llm = self.llm,
            lang = self.args.lang
        )
        self.proc = False

    def extract_last_url(self, chat_history: list):
        """
        Find the latest URL provided by the user and trim the chat history to there.
        """

        url = None
        begin_index = 0
        user_records = list(filter(lambda x: not x["isbot"], chat_history))
        for i, record in enumerate(reversed(user_records)):

            urls_in_msg = re.findall(r'^(https?://[^\s]+)$', record["msg"])
            if len(urls_in_msg) != 0: 
                url = urls_in_msg[-1]
                begin_index = len(chat_history) - i - 1
                break
        
        return url, chat_history[begin_index:]

    async def llm_compute(self, data):
        chat_history = json.loads(data.get("input"))
        auth_token = data.get("kuwa_token") or self.args.api_key
        url = None

        try:
            url, chat_history = self.extract_last_url(chat_history)
            if url == None : raise NoUrlException(i18n.t('webqa.no_url_exception'))
            
            chat_history = [{"isbot": False, "msg": None}] + chat_history[1:]
            async for reply in self.webqa.process(urls=[url], chat_history=chat_history, auth_token=auth_token):
                yield reply

        except NoUrlException as e:
            yield str(e)

        except Exception as e:
            await asyncio.sleep(2) # To prevent SSE error of web page.
            logger.exception('Unexpected error')
            yield i18n.t("webqa.default_exception_msg")

if __name__ == "__main__":
    executor = WebQaExecutor()
    executor.run()