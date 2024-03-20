import asyncio
import aiohttp
import requests
import numpy as np
from typing import (
    Optional, Callable, Any, 
    Tuple, List, Dict
)
from abc import ABC, abstractmethod

class EmbeddingModelAdapter(ABC):
    
    model_name:str

    @abstractmethod
    def embed(self, texts: List[str]) -> List[List[float]]:
        raise NotImplementedError()

    @abstractmethod
    async def aembed(self, texts: List[str]) -> List[List[float]]:
        raise NotImplementedError()

class InfinityEmbeddingClient(EmbeddingModelAdapter):
    
    """
    The following contents are modified from
    TinyAsyncOpenAIInfinityEmbeddingClient from
    "langchain_community/embeddings/infinity.py"

    Example:
        .. code-block:: python

            mini_client = InfinityEmbeddingClient(
                model_name="thenlper/gte-large-zh",
            )
            embeds = mini_client.embed(
                texts=["doc1", "doc2"]
            )
            # or
            embeds = await mini_client.aembed(
                texts=["doc1", "doc2"]
            )

    """

    def __init__(
        self,
        host: str = "http://localhost:8181",
        model_name: str = None,
        aiosession: Optional[aiohttp.ClientSession] = None,
    ) -> None:
        self.host = host
        self.model_name = model_name
        self.aiosession = aiosession

        if self.host is None or len(self.host) < 3:
            raise ValueError(" param `host` must be set to a valid url")
        self._batch_size = 128

    @staticmethod
    def _permute(
        texts: List[str], sorter: Callable = len
    ) -> Tuple[List[str], Callable]:
        """Sort texts in ascending order, and
        delivers a lambda expr, which can sort a same length list
        https://github.com/UKPLab/sentence-transformers/blob/
        c5f93f70eca933c78695c5bc686ceda59651ae3b/sentence_transformers/SentenceTransformer.py#L156

        Args:
            texts (List[str]): _description_
            sorter (Callable, optional): _description_. Defaults to len.

        Returns:
            Tuple[List[str], Callable]: _description_

        Example:
            ```
            texts = ["one","three","four"]
            perm_texts, undo = self._permute(texts)
            texts == undo(perm_texts)
            ```
        """

        if len(texts) == 1:
            # special case query
            return texts, lambda t: t
        length_sorted_idx = np.argsort([-sorter(sen) for sen in texts])
        texts_sorted = [texts[idx] for idx in length_sorted_idx]

        return texts_sorted, lambda unsorted_embeddings: [  # noqa E731
            unsorted_embeddings[idx] for idx in np.argsort(length_sorted_idx)
        ]

    def _batch(self, texts: List[str]) -> List[List[str]]:
        """
        splits Lists of text parts into batches of size max `self._batch_size`
        When encoding vector database,

        Args:
            texts (List[str]): List of sentences
            self._batch_size (int, optional): max batch size of one request.

        Returns:
            List[List[str]]: Batches of List of sentences
        """
        if len(texts) == 1:
            # special case query
            return [texts]
        batches = []
        for start_index in range(0, len(texts), self._batch_size):
            batches.append(texts[start_index : start_index + self._batch_size])
        return batches

    @staticmethod
    def _unbatch(batch_of_texts: List[List[Any]]) -> List[Any]:
        if len(batch_of_texts) == 1 and len(batch_of_texts[0]) == 1:
            # special case query
            return batch_of_texts[0]
        texts = []
        for sublist in batch_of_texts:
            texts.extend(sublist)
        return texts

    def _kwargs_post_request(self, model: str, texts: List[str]) -> Dict[str, Any]:
        """Build the kwargs for the Post request, used by sync

        Args:
            model (str): _description_
            texts (List[str]): _description_

        Returns:
            Dict[str, Collection[str]]: _description_
        """
        return dict(
            url=f"{self.host}/embeddings",
            headers={
                # "accept": "application/json",
                "content-type": "application/json",
            },
            json=dict(
                input=texts,
                model=model,
            ),
        )

    def _sync_request_embed(
        self, model: str, batch_texts: List[str]
    ) -> List[List[float]]:
        response = requests.post(
            **self._kwargs_post_request(model=model, texts=batch_texts)
        )
        if response.status_code != 200:
            raise Exception(
                f"Infinity returned an unexpected response with status "
                f"{response.status_code}: {response.text}"
            )
        return [e["embedding"] for e in response.json()["data"]]

    def embed(self, texts: List[str]) -> List[List[float]]:
        """call the embedding of model

        Args:
            model (str): to embedding model
            texts (List[str]): List of sentences to embed.

        Returns:
            List[List[float]]: List of vectors for each sentence
        """
        perm_texts, unpermute_func = self._permute(texts)
        perm_texts_batched = self._batch(perm_texts)

        # Request
        map_args = (
            self._sync_request_embed,
            [self.model_name] * len(perm_texts_batched),
            perm_texts_batched,
        )
        if len(perm_texts_batched) == 1:
            embeddings_batch_perm = list(map(*map_args))
        else:
            with ThreadPoolExecutor(32) as p:
                embeddings_batch_perm = list(p.map(*map_args))

        embeddings_perm = self._unbatch(embeddings_batch_perm)
        embeddings = unpermute_func(embeddings_perm)
        return embeddings

    async def _async_request(
        self, session: aiohttp.ClientSession, **kwargs: Dict[str, Any]
    ) -> List[List[float]]:
        async with session.post(**kwargs) as response:
            if response.status != 200:
                raise Exception(
                    f"Infinity returned an unexpected response with status "
                    f"{response.status}: {response.text}"
                )
            embedding = (await response.json())["data"]
            return [e["embedding"] for e in embedding]

    async def aembed(self, texts: List[str]) -> List[List[float]]:
        """call the embedding of model, async method

        Args:
            model (str): to embedding model
            texts (List[str]): List of sentences to embed.

        Returns:
            List[List[float]]: List of vectors for each sentence
        """
        perm_texts, unpermute_func = self._permute(texts)
        perm_texts_batched = self._batch(perm_texts)

        # Request
        if self.aiosession is None or self.aiosession.closed:
            self.aiosession = aiohttp.ClientSession(
                trust_env=True, connector=aiohttp.TCPConnector(limit=32)
            )
        async with self.aiosession as session:
            embeddings_batch_perm = await asyncio.gather(
                *[
                    self._async_request(
                        session=session,
                        **self._kwargs_post_request(model=self.model_name, texts=t),
                    )
                    for t in perm_texts_batched
                ]
            )

        embeddings_perm = self._unbatch(embeddings_batch_perm)
        embeddings = unpermute_func(embeddings_perm)
        return embeddings