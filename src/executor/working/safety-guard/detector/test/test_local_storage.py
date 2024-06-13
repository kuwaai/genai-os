import sys, os
import unittest

from typing import Any

sys.path.append(os.path.join(os.path.dirname(__file__), '../../lib'))
sys.path.append(os.path.join(os.path.dirname(__file__), '..'))
from src.local_storage import GuardStorage, EmbeddingCache
from src.target_rules import TargetRules
from src.core.guard_interface import GuardInterface
from model.detector import ChainEnum
from model.rule import ActionEnum

class DummyGuard(GuardInterface):
    def __init__(self):
        self.rules = {}

    async def add_rule(self, rule_id: int, desc: str, black_list: [str], white_list: [str]=[]) -> bool:
        self.rules[rule_id] = dict(black_list=black_list, white_list=white_list)
        return True

    async def score(self, records: [dict[str, str]]) -> dict[int, float]:
        raise NotImplementedError()

    async def check(self, records: [dict[str, str]]) -> dict[int, dict[str, Any]]:
        raise NotImplementedError()

class TestGuardStore(unittest.IsolatedAsyncioTestCase):
    async def test_get(self):
        store = GuardStorage()
        guard = DummyGuard()
        await guard.add_rule(0, 'test-rule', ['123', '456'], [])
        actions = {0: {'action': ActionEnum.block, 'message': None}}
        target_rules = TargetRules(guard=guard, actions=actions)
        model_id = 'test-model-id'
        chain = ChainEnum.pre_filter
        store.insert(
            model_id=model_id,
            chain=chain,
            target_rules=target_rules
        )
        store.commit()
        result = store.get(model_id=model_id, chain=chain)
        self.assertNotEqual(result.guard, guard)
        self.assertEqual(result.guard.rules, guard.rules)
        self.assertEqual(result.actions, actions)
        
        # Test object isolation
        await guard.add_rule(2, 'test-rule2', ['321', '654'], [])
        self.assertNotEqual(result.guard.rules, guard.rules)

    async def test_commit(self):
        store = GuardStorage()
        guard = DummyGuard()
        await guard.add_rule(0, 'test-rule', ['123', '456'], [])
        actions = {0: {'action': ActionEnum.block, 'message': None}}
        target_rules = TargetRules(guard=guard, actions=actions)
        model_id = 'test-model-id'
        chain = ChainEnum.pre_filter
        store.insert(
            model_id=model_id,
            chain=chain,
            target_rules=target_rules
        )
        result = store.get(model_id=model_id, chain=chain)
        self.assertEqual(result, None)

class TestEmbeddingCache(unittest.TestCase):
    
    def test_get_not_exist(self):
        cache = EmbeddingCache()
        self.assertEqual(cache.get('Not existed key'), None)

    def test_set(self):
        cache = EmbeddingCache()
        text = 'test'
        embedding = [1.0, 2.0, 3.0]
        cache.set(text, embedding)
        self.assertEqual(cache.get(text), embedding)

    def test_lru(self):
        cache = EmbeddingCache(cache_size=2)
        texts = ['example text1', 'example text2']
        embeddings = [[1.0], [2.0]]
        for t, e in zip(texts, embeddings):
            cache.set(text=t, embedding=e)
        for t, e in zip(texts, embeddings):
            self.assertEqual(cache.get(t), e)

        test_text = 'test'
        test_embedding = [1.0, 2.0, 3.0]
        cache.set(test_text, test_embedding)
        self.assertEqual(cache.get(texts[0]), None)
        self.assertEqual(cache.get(texts[1]), embeddings[1])
        self.assertEqual(cache.get(test_text), test_embedding)

if __name__ == '__main__':
    unittest.main()