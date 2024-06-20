import sys, os
import copy
from typing import List, Tuple, Dict, Any, Optional
from collections import deque

sys.path.append(os.path.join(os.path.dirname(__file__), '../../lib'))
from .target_rules import TargetRules
from .core.guard_interface import GuardInterface
from model.detector import ChainEnum
from model.rule import ActionEnum

class GuardStorage:
    """
    A KV store to store the indexed rules. To preserve the transaction
    atomicity, the inserting operation won't affect the reading value until
    it's committed.
    Key: (model_id, chain)
    Value: TargetRules
    """
    def __init__(self):
        self.reading_store = dict()
        self.writing_store = dict()
    
    def get(self, model_id: str, chain: ChainEnum) -> None | TargetRules:
        result = None
        key = tuple([model_id, chain])
        if key in self.reading_store:
            result = self.reading_store[key]
        return result
    
    def insert(
        self,
        model_id:str,
        chain:ChainEnum,
        target_rules:TargetRules,
    ):
        key = tuple([model_id, chain])
        value = copy.deepcopy(target_rules)
        self.writing_store[key] = value

    def commit(self):
        """
        Commit the inserting operation.
        Note that this function is not thread-safe.
        """
        del self.reading_store
        self.reading_store = self.writing_store
        self.writing_store = dict()

class EmbeddingCache:
    """
    A LRU cache to store the embedding of sentences in the rule.
    """

    def __init__(self, cache_size:int=5E3):
        self.cache_size = cache_size
        self.queue = deque()
        self.hash_map = dict()

    def _is_queue_full(self):
        return len(self.queue) == self.cache_size

    def set(self, text: str, embedding: List[float]):
        if text in self.hash_map: return
        if self._is_queue_full():
            pop_key = self.queue.pop()
            self.hash_map.pop(pop_key)
        self.queue.appendleft(text)
        self.hash_map[text] = embedding

    def get(self, text: str) -> None | List[float]:
        if text not in self.hash_map: return None
        self.queue.remove(text)
        self.queue.appendleft(text)
        return self.hash_map[text]

# Use singleton pattern to implement a global guard storage and embedding cache
def get_guard_storage(store=GuardStorage()):
    return store

def get_embedding_cache(cache=EmbeddingCache()):
    return cache
