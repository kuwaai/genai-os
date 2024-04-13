#!/bin/python3
# -#- coding: UTF-8 -*-

from typing import Generator
from src.webqa import WebQa
from kuwa.executor import LLMExecutor

import re
import logging
import asyncio
import os
import functools
import itertools
import requests

logger = logging.getLogger(__name__)

class NoUrlException(Exception):
    def __str__(self):
        return "找不到URL。"

class WebQaExecutor(LLMExecutor):
    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        parser.add_argument('--api_base_url', default="http://127.0.0.1/", help='The API base URL of Kuwa multi-chat WebUI')
        parser.add_argument('--api_auth_token', default=None, help='The API authentication token of Kuwa multi-chat WebUI')
        parser.add_argument('--model', default="gemini-pro", help='The model name (access code) on Kuwa multi-chat WebUI')
        parser.add_argument('--mmr_k', default=6, type=int, help='Number of chunk to retrieve after Maximum Marginal Relevance (MMR).')
        parser.add_argument('--mmr_fetch_k', default=12, type=int, help='Number of chunk to retrieve before Maximum Marginal Relevance (MMR).')
        parser.add_argument('--chunk_size', default=512, type=int, help='The charters in the chunk.')
        parser.add_argument('--chunk_overlap', default=128, type=int, help='The overlaps between chunks.')

    def setup(self):

        self.llm = KuwaLlmClient(
            base_url = self.args.api_base_url,
            model=self.args.model,
            auth_token=self.api_auth_token
        )
        self.document_store = DocumentStore(
            mmr_k = self.args.mmr_k,
            mmr_fetch_k = self.args.mmr_fetch_k,
            chunk_size = self.args.chunk_size,
            chunk_overlap = self.args.chunk_overlap
        )
        self.app = WebQa(
            document_store = self.document_store,
            llm = self.llm,
        )
        self.proc = False

    def extract_last_url(self, chat_history: list):
        """
        Find the latest URL provided by the user and trim the chat history to there.
        """

        url = None
        begin_index = 0
        user_records = filter(lambda x: x["role"] == "user", chat_history)
        for i, record in enumerate(reversed(user_records)):

            urls_in_msg = re.findall(r'^(https?://[^\s]+)$', record["content"])
            if len(urls_in_msg) != 0: 
                url = urls_in_msg[-1]
                begin_index = len(chat_history) - i - 1
                break

        return url, chat_history[begin_index:]

    async def llm_compute(self, data):
        msg = json.loads(data.get("input"))
        url = None

        try:
        
            url, chat_history = self.extract_last_url(chat_history)
            if url == None : raise NoUrlException
            
            chat_history = [ChatRecord(msg=None, role=Role.USER)] + chat_history[1:]
            async for reply in self.app.process([url], chat_history):
                yield reply

        except NoUrlException as e:
            yield str(e)

        except Exception as e:
            await asyncio.sleep(2) # To prevent SSE error of web page.
            self.logger.exception('Unexpected error')
            yield '發生錯誤，請再試一次或是聯絡管理員。'

if __name__ == "__main__":
    executor = WebQaExecutor()
    executor.run()