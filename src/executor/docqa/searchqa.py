#!/bin/python3
# -#- coding: UTF-8 -*-

import os
import re
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
from kuwa.executor import LLMExecutor

from src.docqa import DocQa
from src.kuwa_llm_client import KuwaLlmClient
from src.document_store import DocumentStore

logger = logging.getLogger(__name__)

class NoUrlException(Exception):
    def __init__(self, msg):
        self.msg = msg
    def __str__(self):
        return self.msg

class SearchQaExecutor(LLMExecutor):
    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        parser.add_argument('--visible_gpu', default=None, help='Specify the GPU IDs that this executor can use. Separate by comma.')
        parser.add_argument('--lang', default="en", help='The language code to internationalize the aplication. See \'lang/\'')
        parser.add_argument('--api_base_url', default="http://127.0.0.1/", help='The API base URL of Kuwa multi-chat WebUI')
        parser.add_argument('--api_key', default=None, help='The API authentication token of Kuwa multi-chat WebUI')
        parser.add_argument('--limit', default=3072, type=int, help='The limit of the LLM\'s context window')
        parser.add_argument('--model', default=None, help='The model name (access code) on Kuwa multi-chat WebUI')
        parser.add_argument('--mmr_k', default=6, type=int, help='Number of chunk to retrieve after Maximum Marginal Relevance (MMR).')
        parser.add_argument('--mmr_fetch_k', default=12, type=int, help='Number of chunk to retrieve before Maximum Marginal Relevance (MMR).')
        parser.add_argument('--chunk_size', default=512, type=int, help='The charters in the chunk.')
        parser.add_argument('--chunk_overlap', default=128, type=int, help='The overlaps between chunks.')
        parser.add_argument('--google_api_key', default=None, help='The API key of Google API.')
        parser.add_argument('--google_cse_id', default=None, help='The ID of Google Custom Search Engine.')
        parser.add_argument('--restricted_sites', default='', help='A list of restricted sites. Septate by ";".')
        parser.add_argument('--blocked_sites', default='youtube.com;facebook.com;instagram.com;twitter.com;threads.net;play.google.com;apps.apple.com;www.messenger.com', help='A list of blocked sites. Septate by ";".')
        parser.add_argument('--num_url', default=3, type=int, help='Search results reference before RAG.')
        parser.add_argument('--user_agent', default="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36",
                                            help='The user agent string when issuing the crawler.')
        parser.add_argument('--hide_ref', action="store_true", help="Do not show the reference at the end.")

    def setup(self):
        i18n.load_path.append(f'lang/{self.args.lang}/')
        i18n.config.set("error_on_missing_translation", True)
        i18n.config.set("locale", self.args.lang)

        if self.args.visible_gpu:
            os.environ["CUDA_VISIBLE_DEVICES"] = self.args.visible_gpu

        self.llm = KuwaLlmClient(
            base_url = self.args.api_base_url,
            kernel_base_url = self.kernel_url,
            model=self.args.model,
            auth_token=self.args.api_key
        )
        self.document_store = DocumentStore(
            mmr_k = self.args.mmr_k,
            mmr_fetch_k = self.args.mmr_fetch_k,
            chunk_size = self.args.chunk_size,
            chunk_overlap = self.args.chunk_overlap
        )
        self.docqa = DocQa(
            document_store = self.document_store,
            llm = self.llm,
            lang = self.args.lang,
            with_ref=False,
            user_agent=self.args.user_agent
        )

        self.google_api_key = self.args.google_api_key
        self.searching_engine_id = self.args.google_cse_id
        self.restricted_sites = self.args.restricted_sites
        self.blocked_sites = self.args.blocked_sites
        self.num_url = self.args.num_url
        self.user_agent = self.args.user_agent
        self.with_ref = not self.args.hide_ref

        self.proc = False
        
        self.document_store.load_embedding_model()

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
            logger.exception(e)
        finally:
            return resp != None and resp.ok

    async def search_url(self, chat_history: [dict], parsed_modelfile) -> ([dict[str, str]],[str]):
        """
        Get first URL from the search result.
        """

        process_site_list = lambda x: list(filter(None, x.split(';')))
        restricted_sites = process_site_list(self.restricted_sites)
        blocked_sites = process_site_list(self.blocked_sites)
        latest_user_record = next(filter(lambda x: not x["isbot"], reversed(chat_history)))
        latest_user_msg = latest_user_record["msg"]

        before_prompt = parsed_modelfile.before_prompt
        after_prompt = parsed_modelfile.after_prompt
        query = "{} {} {}".format(before_prompt, latest_user_msg, after_prompt)
        query += ''.join([f' site:{s.strip()}' for s in restricted_sites])
        query += ''.join([f' -site:{s.strip()}' for s in blocked_sites])
        
        logger.debug(f'Restricted sites: {restricted_sites}')
        logger.debug(f'Blocked sites: {blocked_sites}')
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
            titles = [item['title'] for item in resp['items']]
            urls_reachable = await asyncio.gather(*[self.is_url_reachable(url) for url in urls])
            logger.debug(list(zip(urls, urls_reachable)))
            urls = list(zip(urls, titles))
            urls = list(itertools.compress(urls, urls_reachable))
            urls = urls[:min(len(urls), self.num_url)]
        
        except Exception as e:
            logger.exception('Error while getting URLs for Google searching API')
        
        finally:
            return urls, [latest_user_record]

    async def llm_compute(self, data):
        chat_history = json.loads(data.get("input"))
        auth_token = data.get("user_token") or self.args.api_key
        parsed_modelfile = self.parse_modelfile(data.get("modelfile", "[]"))
        override_qa_prompt = parsed_modelfile.override_system_prompt

        try:
        
            urls, chat_history = await self.search_url(chat_history, parsed_modelfile)

            if len(urls) == 0: raise NoUrlException(i18n.t("searchqa.search_unreachable"))
        
            self.proc = True
            response_generator = self.docqa.process(
                urls=[u for u,t in urls],
                chat_history=chat_history,
                auth_token=auth_token,
                override_qa_prompt=override_qa_prompt
            )
            async for reply in response_generator:
                if not self.proc:
                    await response_generator.aclose()
                yield reply
        
            if not self.with_ref:
                return
            
            yield f"\n\n{i18n.t('searchqa.reference')}\n"
            for i, (url, title) in enumerate(urls):
                yield f'{i+1}. [{title.strip()}]({url})\n'
        
        except NoUrlException as e:
            await asyncio.sleep(2) # To prevent SSE error of web page.
            yield 

        except Exception as e:
            await asyncio.sleep(2) # To prevent SSE error of web page.
            logger.exception('Unexpected error')
            yield i18n.t("searchqa.default_exception_msg")

    async def abort(self):
        if self.proc:
            self.proc = False
            logger.debug("aborted")
            return "Aborted"
        return "No process to abort"

if __name__ == "__main__":
    executor = SearchQaExecutor()
    executor.run()