#!/bin/python3
# -#- coding: UTF-8 -*-

import os
import re
import sys
import gc
import logging
import asyncio
import functools
import itertools
import requests
import json
import i18n
import pathlib
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from typing import Generator
from urllib.parse import urljoin
from kuwa.executor import LLMExecutor, Modelfile
from kuwa.executor.modelfile import ParameterDict
from kuwa.executor.llm_executor import extract_last_url
from kuwa.client import KuwaClient

from src.docqa import DocQa
from src.document_store import DocumentStore
from src.document_store_factory import DocumentStoreFactory
from src.crawler import Crawler

logger = logging.getLogger(__name__)

class NoUrlException(Exception):
    def __init__(self, msg):
        self.msg = msg
    def __str__(self):
        return self.msg

class DocQaExecutor(LLMExecutor):
    def __init__(self):
        super().__init__()
        
    def extend_arguments(self, parser):
        parser.add_argument('--visible_gpu', default=None, help='Specify the GPU IDs that this executor can use. Separate by comma.')
        parser.add_argument('--lang', default="en", help='The language code to internationalize the aplication. See \'lang/\'')

        crawler_group = parser.add_argument_group('Crawler Options')
        crawler_group.add_argument('--user_agent', default="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36",
                                            help='The user agent string when issuing the crawler.')
        crawler_group.add_argument('--max_depth', type=int, default=1,
                                            help='How depth should the crawler go. Set to value greater than 1 to enable recursive crawling.')

        retriever_group = parser.add_argument_group('Retriever Options')
        retriever_group.add_argument('--database', default=None, type=str, help='The path the the pre-built database.')
        retriever_group.add_argument('--embedding_model', default="intfloat/multilingual-e5-small", help='The HuggingFace name of the embedding model.')
        retriever_group.add_argument('--mmr_k', default=6, type=int, help='Number of chunk to retrieve after Maximum Marginal Relevance (MMR).')
        retriever_group.add_argument('--mmr_fetch_k', default=12, type=int, help='Number of chunk to retrieve before Maximum Marginal Relevance (MMR).')
        retriever_group.add_argument('--chunk_size', default=512, type=int, help='The charters in the chunk.')
        retriever_group.add_argument('--chunk_overlap', default=128, type=int, help='The overlaps between chunks.')
        retriever_group.add_argument('--vector_db_ttl_sec', default=600, type=int, help='The duration of a cached on-demand vector database remains active before requiring an update from the source.')
        
        generator_group = parser.add_argument_group('Generator Options')
        generator_group.add_argument('--api_base_url', default="http://127.0.0.1/", help='The API base URL of Kuwa multi-chat WebUI')
        generator_group.add_argument('--api_key', default=None, help='The API authentication token of Kuwa multi-chat WebUI')
        generator_group.add_argument('--limit', default=3072, type=int, help='The limit of the LLM\'s context window')
        generator_group.add_argument('--model', default=None, help='The model name (access code) on Kuwa multi-chat WebUI')
        
        display_group = parser.add_argument_group('Display Options')
        display_group.add_argument('--hide_ref', action="store_true", help="Do not show the reference at the end.")
        display_group.add_argument('--hide_ref_content', action="store_true", help="Do not show the content of references.")
        

    def setup(self):

        if self.args.visible_gpu:
            os.environ["CUDA_VISIBLE_DEVICES"] = self.args.visible_gpu

        self.document_store_factory = DocumentStoreFactory(
            ttl_sec = self.args.vector_db_ttl_sec
        )

        loop = asyncio.get_event_loop()
        task = loop.create_task(self._app_setup())
        loop.run_until_complete(task)
        

    async def _app_setup(self, params:ParameterDict=ParameterDict()):
        general_params = params["_"]
        crawler_params = params["crawler_"]
        retriever_params = params["retriever_"]
        generator_params = params["generator_"]
        display_params = params["display_"]
        
        self.lang = general_params.get("lang", self.args.lang)
        self.lang = self.lang if self.lang in os.listdir("lang/") else "en"
        i18n.load_path.append(f'lang/{self.lang}/')
        i18n.config.set("error_on_missing_translation", True)
        i18n.config.set("fallback", "en")
        i18n.config.set("locale", self.lang)

        self.pre_built_db = retriever_params.get("database", self.args.database)
        self.with_ref = not display_params.get("hide_ref", self.args.hide_ref)
        self.display_ref_content = not display_params.get("hide_ref_content", self.args.hide_ref_content)
        self.llm = KuwaClient(
            base_url = self.args.api_base_url,
            kernel_base_url = self.kernel_url,
            model=generator_params.get("model", self.args.model),
            auth_token=general_params.get("user_token", self.args.api_key),
            limit=generator_params.get("limit", self.args.limit)
        )
        self.document_store_kwargs = dict(
            embedding_model = retriever_params.get("embedding_model", self.args.embedding_model),
            chunk_size = retriever_params.get("chunk_size", self.args.chunk_size),
            chunk_overlap = retriever_params.get("chunk_overlap", self.args.chunk_overlap)
        )
        self.retriever_param = {
            'k': retriever_params.get("mmr_k", self.args.mmr_k),
            'fetch_k': retriever_params.get("mmr_fetch_k", self.args.mmr_fetch_k),
        }
        self.crawler = Crawler(
            user_agent = crawler_params.get("user_agent", self.args.user_agent),
            max_depth = crawler_params.get("max_depth", self.args.max_depth)
        )
        self.document_store_factory.set_crawler(self.crawler)
        self.docqa = DocQa(
            llm = self.llm,
            lang = self.lang,
            with_ref = self.with_ref,
            display_ref_content = self.display_ref_content,
        )
        self.proc = False

        # Pre-warm
        if self.pre_built_db is not None:
            document_store = await self.document_store_factory.load_document_store(self.pre_built_db)

    async def pre_load_db(self, db_path):
        loop = asyncio.get_running_loop()
        await loop.run_in_executor(None, document_store.load_embedding_model)
        return document_store

    async def doc_qa(
        self,
        urls: [str],
        chat_history: [dict],
        modelfile:Modelfile,
    ):
        auth_token = modelfile.parameters["_"]["user_token"] or self.args.api_key
        document_store = None
        docs = None
        if self.pre_built_db is not None:
            document_store = await self.document_store_factory.load_document_store(self.pre_built_db)
        else:
            try:
                document_store, docs = await self.document_store_factory.construct_document_store(
                    urls = urls,
                    document_store_kwargs = self.document_store_kwargs
                )
            except Exception:
                logger.exception("Error when constructing document store.")
                yield i18n.t('docqa.error_fetching_document')
                return
        document_store.init_retriever(self.retriever_param)

        self.proc = True
        response_generator = self.docqa.process(
            document_store=document_store,
            docs=docs,
            chat_history=chat_history,
            auth_token=auth_token,
            modelfile=modelfile,
        )
        
        async for reply in response_generator:
            if not self.proc:
                await response_generator.aclose()
            yield reply
    
    async def llm_compute(self, history: list[dict], modelfile:Modelfile):
        await self._app_setup(params=modelfile.parameters)

        try:
            url = None
            if self.pre_built_db is None:
                url, history = extract_last_url(history)
                if url is None:
                    raise NoUrlException(i18n.t('docqa.no_url_exception'))
            response_generator = self.doc_qa(
                urls = [url],
                chat_history = history,
                modelfile=modelfile,
            )
            
            async for reply in response_generator:
                yield reply

        except NoUrlException as e:
            yield str(e)

        except Exception as e:
            await asyncio.sleep(2) # To prevent SSE error of web page.
            logger.exception('Unexpected error')
            yield i18n.t("docqa.default_exception_msg")+'\n'
            yield str(e)
        finally:
            logger.info("Done")

    async def abort(self):
        if self.proc:
            self.proc = False
            logger.debug("aborted")
            return "Aborted"
        return "No process to abort"

if __name__ == "__main__":
    executor = DocQaExecutor()
    executor.run()