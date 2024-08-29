#!/bin/python3
# -#- coding: UTF-8 -*-

import os
import re
import gc
import sys
import logging
import asyncio
import functools
import itertools
import requests
import json
import i18n
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from typing import Generator
from kuwa.executor import LLMExecutor, Modelfile
from kuwa.executor.modelfile import ParameterDict
from kuwa.client import KuwaClient

from docqa import DocQaExecutor, NoUrlException

logger = logging.getLogger(__name__)

class SearchQaExecutor(LLMExecutor):
    doc_qa: DocQaExecutor

    def __init__(self):
        self.doc_qa = DocQaExecutor()
        super().__init__()

    def extend_arguments(self, parser):
        self.doc_qa.extend_arguments(parser)
        
        search_group = parser.add_argument_group('Search Engine Options')
        search_group.add_argument('--google_api_key', default=None, help='The API key of Google API.')
        search_group.add_argument('--google_cse_id', default=None, help='The ID of Google Custom Search Engine.')
        search_group.add_argument('--advanced_search_params', default='-site:youtube.com -site:facebook.com -site:instagram.com -site:twitter.com -site:threads.net -site:play.google.com -site:apps.apple.com -site:www.messenger.com', help='Advanced search parameters')
        search_group.add_argument('--num_url', default=3, type=int, help='Search results reference before RAG.')
        
    def setup(self):
        self.doc_qa.args = self.args
        self.doc_qa.setup()

        if self.args.visible_gpu:
            os.environ["CUDA_VISIBLE_DEVICES"] = self.args.visible_gpu
        
        self.google_api_key = self.args.google_api_key
        self.searching_engine_id = self.args.google_cse_id
        self.user_agent = self.args.user_agent

        loop = asyncio.get_event_loop()
        task = loop.create_task(self._app_setup())
        loop.run_until_complete(task)

    async def _app_setup(self, params:ParameterDict=ParameterDict()):
        
        await self.doc_qa._app_setup(params=params)
        
        search_params = params["search_"]

        self.advanced_search_params = search_params.get("advanced_params", self.args.advanced_search_params)
        self.num_url = search_params.get("num_url", self.args.num_url)

    async def is_url_reachable(self, url:str, timeout=5) -> bool:
        loop = asyncio.get_running_loop()
        resp = None
        try:
            resp = await loop.run_in_executor(
                None,
                functools.partial(
                requests.get,
                url,
                timeout=timeout,
                headers = {} if self.user_agent is None else {"User-Agent": self.user_agent},
                verify=False
                )
            )
        except Exception as e:
            logger.debug(str(e))
        finally:
            return resp != None and resp.ok

    async def search_url(self, chat_history: [dict], parsed_modelfile) -> ([dict[str, str]],[str]):
        """
        Get first URL from the search result.
        """

        latest_user_record = next(filter(lambda x: x["role"] == "user", reversed(chat_history)))
        latest_user_msg = latest_user_record["content"]

        query = "{user} {params}".format(user=latest_user_msg, params=self.advanced_search_params)
        logger.debug(f'Query: {query}')

        endpoint = 'https://customsearch.googleapis.com/customsearch/v1'
        params = {
            'key': self.google_api_key,
            'cx': self.searching_engine_id,
            'q': query
        }

        urls = []

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
            
            logger.debug(f'Search response ({resp.status_code}):\n{resp.content}')

            if not resp.ok or 'error' in resp.json(): raise ValueError()
            resp  = resp.json()
            
            urls = [item['link'] for item in resp['items']]
            # titles = [item['title'] for item in resp['items']]
            urls_reachable = await asyncio.gather(*[self.is_url_reachable(url) for url in urls])
            logger.debug(list(zip(urls, urls_reachable)))
            # urls = list(zip(urls, titles))
            urls = list(itertools.compress(urls, urls_reachable))
            urls = urls[:min(len(urls), self.num_url)]
        
        except Exception as e:
            logger.exception('Error while getting URLs for Google searching API')
        
        finally:
            return urls, [latest_user_record]

    async def llm_compute(self, history: list[dict], modelfile:Modelfile):
        await self._app_setup(params=modelfile.parameters)
        auth_token = modelfile.parameters["_"]["user_token"] or self.args.api_key

        try:
        
            urls, history = await self.search_url(history, modelfile)

            if len(urls) == 0: raise NoUrlException(i18n.t("searchqa.search_unreachable"))

            history[-1]['content'] += ' '.join(urls)
        
            self.proc = True
            response_generator = self.doc_qa.llm_compute(
                history=history,
                modelfile=modelfile
            )
            async for reply in response_generator:
                if not self.proc:
                    await response_generator.aclose()
                yield reply

        except NoUrlException as e:
            await asyncio.sleep(2) # To prevent SSE error of web page.
            yield 

        except Exception as e:
            await asyncio.sleep(2) # To prevent SSE error of web page.
            logger.exception('Unexpected error')
            yield i18n.t("searchqa.default_exception_msg")
        finally:
            logger.info("Done")

    async def abort(self):
        if self.proc:
            self.proc = False
            await self.doc_qa.abort()
            logger.debug("aborted")
            return "Aborted"
        return "No process to abort"

if __name__ == "__main__":
    executor = SearchQaExecutor()
    executor.run()