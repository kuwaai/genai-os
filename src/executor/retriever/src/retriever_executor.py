#!/bin/python3
# -#- coding: UTF-8 -*-

import os
import re
import sys
import logging
import json
import functools
from kuwa.executor import LLMExecutor, Modelfile
from kuwa.executor.util import merge_config
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from .recursive_url_multimedia_loader import RecursiveUrlMultimediaLoader
from .document_store import DocumentStore, DocumentStoreFactory
from .embedding_model_store import EmbeddingModelStore
from .parallel_splitter import ParallelSplitter

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
        crawler_group = parser.add_argument_group('Crawler Options')
        crawler_group.add_argument('--csr_threshold', default=100, type=int, help='If extracted charters below this value, we will launch a real browser to fetch the content.')
        crawler_group.add_argument('--max_depth', default=1, type=int, help='The maximum depth to download the pages recursively.')
        crawler_group.add_argument('--prevent_outside', default=False, action="store_true", help='Prevent the recursion outside the root.')
        crawler_group.add_argument('--timeout', default=10, type=int, help='Timeout to download the page.')
        crawler_group.add_argument('--user_agent', default="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36",
                                            help='The user agent string when issuing the crawler.')

        splitter_group = parser.add_argument_group('Splitter Options')
        splitter_group.add_argument('--chunk_size', default=512, type=int, help='The size of each chunk in char.')
        splitter_group.add_argument('--chunk_overlap', default=128, type=int, help='The overlap between chunks in char.')

        embedding_model_group = parser.add_argument_group('Embedding Model Options')
        embedding_model_group.add_argument('--default_embedding_model', default="thenlper/gte-base-zh", help='Name of the default embedding model.')
        embedding_model_group.add_argument('--n_cached_embedding_model', default=3, type=int, help='Maximum embedding model to cache.')

        retrieving_group = parser.add_argument_group('Retrieving Options')
        retrieving_group.add_argument('--mmr_fetch_k', default=12, type=int, help='Number of chunk to retrieve before Maximum Marginal Relevance (MMR).')
        retrieving_group.add_argument('--mmr_k', default=6, type=int, help='Number of chunk to retrieve after Maximum Marginal Relevance (MMR).')

    def setup(self):
        crawler_config = {}
        crawler_config['csr_threshold'] = self.args.csr_threshold
        crawler_config['max_depth'] = self.args.max_depth
        crawler_config['prevent_outside'] = self.args.prevent_outside
        crawler_config['timeout'] = self.args.timeout
        crawler_config['user_agent'] = self.args.user_agent
        self.crawler_config = crawler_config

        retriever_config = {}
        retriever_config['chunk_size'] = self.args.chunk_size
        retriever_config['chunk_overlap'] = self.args.chunk_overlap
        retriever_config['embedding_model_name'] = self.args.default_embedding_model
        retriever_config['mmr_fetch_k'] = self.args.mmr_fetch_k
        retriever_config['mmr_k'] = self.args.mmr_k
        self.retriever_config = retriever_config

        self.embedding_model_store = EmbeddingModelStore(n_cached_model=self.args.n_cached_embedding_model)

        self.proc = False

    def extract_last_url(self, chat_history: list):
        """
        Find the latest URL provided by the user and trim the chat history to there.
        """

        url = None
        begin_index = 0
        user_records = list(filter(lambda x: x["role"] == "user", chat_history))
        for i, record in enumerate(reversed(user_records)):

            urls_in_msg = re.findall(r'^(https?://[^\s]+)$', record["content"])
            if len(urls_in_msg) != 0: 
                url = urls_in_msg[-1]
                begin_index = len(chat_history) - i - 1
                break
        
        return url, chat_history[begin_index:]
  
    def get_final_user_input(self, chat_history: [dict]) -> str:
        final_user_record = next(filter(lambda x: x['role'] == "user", reversed(chat_history)))
        return final_user_record['content']

    async def _fetch_documents(self, url:str, modelfile:Modelfile=None):
        """
        Fetch documents from URL.
        """
        crawler_config = merge_config(self.crawler_config, modelfile.parameters)
        
        logger.info(f'Fetching URL "{url}"')
        loader = RecursiveUrlMultimediaLoader(
            url=url,
            max_depth=crawler_config['max_depth'],
            prevent_outside=crawler_config['prevent_outside'],
            timeout=crawler_config['timeout'],
            csr_threshold=crawler_config['csr_threshold'],
            forge_user_agent=crawler_config['user_agent'],
            use_async = True,
            cache_proxy_url = os.environ.get('HTTP_CACHE_PROXY', None)
        ) 
        docs = await loader.async_load()
        docs_len = map(lambda x: len(x.page_content), docs)
        total_docs_len = functools.reduce(lambda x, y: x+y, docs_len)
        logger.info(f'Fetched {len(docs)} documents. The total length of the documents is {total_docs_len}')

        return docs, total_docs_len

    async def _get_retrieve_chunks(self, docs:list[str], query:str, modelfile:Modelfile=None):
        """
        Retrieve the relevant chunks.
        """
        retriever_config = merge_config(self.retriever_config, modelfile.parameters)
        logger.debug(f"Retriever config: {retriever_config}")

        splitter = ParallelSplitter(
            chunk_size=retriever_config['chunk_size'],
            chunk_overlap=retriever_config['chunk_overlap']
        )
        embedding_model = await self.embedding_model_store.aload_model(retriever_config['embedding_model_name'])
        doc_store_factory = DocumentStoreFactory(
            splitter=splitter,
            embedding_model=embedding_model
        )
        doc_store = await doc_store_factory.from_documents(docs)
        retriever = doc_store.get_retriever(
            k=retriever_config['mmr_k'],
            fetch_k=retriever_config['mmr_fetch_k']
        )
        relevant_chunks = await retriever.ainvoke(query)
        logger.info(f'Got {len(relevant_chunks)} relevant chunks.')
        logger.debug(f"Chunks: {relevant_chunks}")
        return relevant_chunks
    
    async def llm_compute(self, history: list[dict], modelfile:Modelfile):
        logger.debug(f"Parameters: {modelfile.parameters}")
        url = None

        try:
            url, history = self.extract_last_url(history)
            if url == None : raise NoUrlException("URL not found")

            self.proc = True
            final_user_input = self.get_final_user_input(history)
            
            docs, total_docs_len = await self._fetch_documents(url, modelfile)
            relevant_chunks = await self._get_retrieve_chunks(docs, final_user_input, modelfile)
            get_content = lambda x: x.page_content

            yield json.dumps({
                "succeed": True,
                "content-length": total_docs_len,
                "content": list(map(get_content, docs)),
                "chunk": list(map(get_content, relevant_chunks))
            })

        except NoUrlException as e:
            yield json.dumps({"succeed": False, "msg": str(e)})

        except Exception as e:
            logger.exception('Unexpected error')
            yield json.dumps({"succeed": False, "msg": str(e)})
        
        finally:
            self.proc = False
    
    async def abort(self):
        if self.proc:
            self.proc = False
            logger.debug("aborted")
            return "Aborted"
        return "No process to abort"