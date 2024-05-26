#!/bin/python3
# -#- coding: UTF-8 -*-

import os
import re
import sys
import logging
import json
from kuwa.executor import LLMExecutor
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from .recursive_url_multimedia_loader import RecursiveUrlMultimediaLoader

logger = logging.getLogger(__name__)

class NoUrlException(Exception):
    def __init__(self, msg):
        self.msg = msg
    def __str__(self):
        return self.msg

class RetrieverExecutor(LLMExecutor):
    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        parser.add_argument('--csr_threshold', default=100, type=int, help='If extracted charters below this value, we will launch a real browser to fetch the content.')
        parser.add_argument('--max_depth', default=1, type=int, help='The maximum depth to download the pages recursively.')
        parser.add_argument('--prevent_outside', default=False, action="store_true", help='Prevent the recursion outside the root.')
        parser.add_argument('--timeout', default=10, type=int, help='Timeout to download the page.')

    def setup(self):
        self.csr_threshold = self.args.csr_threshold
        self.max_depth = self.args.max_depth
        self.prevent_outside = self.args.prevent_outside
        self.timeout = self.args.timeout
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
        url = None

        try:
            url, chat_history = self.extract_last_url(chat_history)
            if url == None : raise NoUrlException("URL not found")

            self.proc = True
            chat_history = [{"isbot": False, "msg": None}] + chat_history[1:]
            
            # Fetching documents
            logger.info(f'Fetching URL "{url}"')
            loader = RecursiveUrlMultimediaLoader(
                url=url,
                max_depth=self.max_depth,
                prevent_outside=self.prevent_outside,
                timeout=self.timeout,
                csr_threshold=self.csr_threshold,
                use_async = True,
                cache_proxy_url = os.environ.get('HTTP_CACHE_PROXY', None)
            ) 
            docs = await loader.async_load()

            logger.info(f'Fetched {len(docs)} documents.')
            for doc in docs:
                if not self.proc: break
                logger.debug(doc.page_content)
                yield doc.page_content

        except NoUrlException as e:
            yield str(e)

        except Exception as e:
            logger.exception('Unexpected error')
            yield str(e)
        
        finally:
            self.proc = False
    
    async def abort(self):
        if self.proc:
            self.proc = False
            logger.debug("aborted")
            return "Aborted"
        return "No process to abort"