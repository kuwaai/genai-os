#!/bin/python3
# -#- coding: UTF-8 -*-

from typing import Generator
from src.docqa import DocumentQa
from kuwa.executor import LLMWorker

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

class WebQaWorker(LLMWorker):
    def __init__(self):
        super().__init__()

    def _create_parser(self):
        parser = super()._create_parser()
        parser.add_argument('--api_endpoint', default="http://127.0.0.1/v1.0/chat/completions", help='The API endpoint of Kuwa multi-chat WebUI')
        parser.add_argument('--api_key', default=None, help='The API key of Kuwa multi-chat WebUI')
        parser.add_argument('--model', default="gemini-pro", help='The model name (access code) on Kuwa multi-chat WebUI')
        return parser

    def _setup(self):
        super()._setup()

        os.environ['KUWA_API_ENDPOINT'] = self.args.api_endpoint
        os.environ['KUWA_API_KEY'] = self.args.api_key
        os.environ['KUWA_MODEL'] = self.args.model
        if not os.environ['KUWA_API_KEY']:
            raise ValueError("You must supply the API key. Run with --help for more information.")

        self.app = WebQa()
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
        try:
            chatgpt_apitoken = data.get("chatgpt_apitoken")
            if not chatgpt_apitoken: chatgpt_apitoken = self.args.api_key
            msg = [{"content":i['msg'], "role":"assistant" if i['isbot'] else "user"} for i in eval(data.get("input").replace("true","True").replace("false","False"))]
        
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
            
    

    async def process(self, chat_history: [ChatRecord]) -> Generator[str, None, None]:

        