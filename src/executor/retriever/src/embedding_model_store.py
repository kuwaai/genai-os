import asyncio
import logging
from langchain_community.embeddings import HuggingFaceEmbeddings
from collections import deque

logger = logging.getLogger(__name__)

class LRUCache:
    def __init__(self, cache_size):
        self.cache_size = cache_size
        self.queue = deque()
        self.hash_map = dict()

    def is_queue_full(self):
        return len(self.queue) == self.cache_size

    def set(self, key, value):
        if key not in self.hash_map:
            if self.is_queue_full():
                pop_key = self.queue.pop()
                self.hash_map.pop(pop_key)
                self.queue.appendleft(key)
                self.hash_map[key] = value
            else:
                self.queue.appendleft(key)
                self.hash_map[key] = value

    def get(self, key):
        if key not in self.hash_map:
            return None
        else:
            self.queue.remove(key)
            self.queue.appendleft(key)
            return self.hash_map[key]

class EmbeddingModelStore:
    def __init__(self, n_cached_model=3):
        self.cache = LRUCache(n_cached_model)

    def load_model(self, model_name="intfloat/multilingual-e5-small"):
        """
        Load a embedding model.
        model_name: The sentence-transformers pre-trained model
        - 'paraphrase-multilingual-mpnet-base-v2' // Size: ~1.11GB
        - 'paraphrase-multilingual-MiniLM-L12-v2' // Size: ~471MB
        - 'infgrad/stella-base-zh' // Chinese embedding model // Size: ~210MB
        """
        model = self.cache.get(model_name)
        if model is None:
            logger.info(f'Loading embedding model {model_name}...')
            model = HuggingFaceEmbeddings(model_name=model_name)
            self.cache.set(model_name, model)
            logger.info(f'Embedding model {model_name} loaded.')
        return model
    
    async def aload_model(self, model_name:str="intfloat/multilingual-e5-small"):
        loop = asyncio.get_running_loop()
        model = await loop.run_in_executor(None, self.load_model, model_name)
        return model
