import unittest

from llm_safety_guard import LlmSafetyGuard
from llm_safety_guard.detection_client import ActionEnum
from llm_safety_guard.target_cache import get_target_cache

class DummyDetector:
    def __init__(
        self,
        pre_filter_action=ActionEnum.none,
        post_filter_action=ActionEnum.none,
        post_filter_trigger_cnt=2
    ):
        self.pre_filter_action = pre_filter_action
        self.post_filter_action = post_filter_action
        self.post_filter_trigger_cnt = post_filter_trigger_cnt
        self.post_filter_cnt = 0
    
    def pre_filter(self, chat_history:[dict], model_id:str) -> (bool, ActionEnum, str|None):
        return (self.pre_filter_action==ActionEnum.none, self.pre_filter_action, 'msg')
    
    def post_filter(self, chat_history:[dict], chunk:str, model_id:str) -> (bool, ActionEnum, str|None):
        self.post_filter_cnt += 1
        if self.post_filter_cnt < self.post_filter_trigger_cnt:
            return (True, ActionEnum.none, '')
        else:
            return (self.post_filter_action==ActionEnum.none, self.post_filter_action, 'msg')

    def is_online(self) -> bool:
        return True

def dummy_generator(chat_history, model_id):
    def gen():
        passage = "在一疊舊照片中，有一張照片深深地烙印在我的記憶裡。這張照片的印象深刻的原因，是它記錄了我成長的過程、與他人的情景、環境的變遷和美麗的景色"
        for c in passage:
            yield c
    return gen(), {'Content-Type': 'text/plain'}

class TestLlmSafetyGuard(unittest.TestCase):
    def test_bypass(self):
        safety_guard = LlmSafetyGuard()
        safety_guard.detector = DummyDetector()
        get_target_cache().targets=[]
        gen = safety_guard.guard(dummy_generator)
        result, other_data = gen(chat_history=[], model_id='test-model')
        self.assertEqual(other_data, {'Content-Type': 'text/plain'})
        result = ''.join([s for s in result])
        expected_result = "在一疊舊照片中，有一張照片深深地烙印在我的記憶裡。這張照片的印象深刻的原因，是它記錄了我成長的過程、與他人的情景、環境的變遷和美麗的景色"
        self.assertEqual(result, expected_result)

    def test_guard(self):
        safety_guard = LlmSafetyGuard()
        safety_guard.detector = DummyDetector()
        get_target_cache().targets=['test-model']
        gen = safety_guard.guard(dummy_generator)
        result = ''.join([s for s in gen(chat_history=[], model_id='test-model')[0]])
        expected_result = "在一疊舊照片中，有一張照片深深地烙印在我的記憶裡。這張照片的印象深刻的原因，是它記錄了我成長的過程、與他人的情景、環境的變遷和美麗的景色"
        self.assertEqual(result, expected_result)

    def test_pre_filter_warn(self):
        safety_guard = LlmSafetyGuard()
        safety_guard.detector = DummyDetector(pre_filter_action=ActionEnum.warn)
        get_target_cache().targets=['test-model']
        gen = safety_guard.guard(dummy_generator)
        result = ''.join([s for s in gen(chat_history=[], model_id='test-model')[0]])
        expected_result = "<<<WARNING>>>msg<<</WARNING>>>在一疊舊照片中，有一張照片深深地烙印在我的記憶裡。這張照片的印象深刻的原因，是它記錄了我成長的過程、與他人的情景、環境的變遷和美麗的景色"
        self.assertEqual(result, expected_result)
    
    def test_pre_filter_block(self):
        safety_guard = LlmSafetyGuard()
        safety_guard.detector = DummyDetector(pre_filter_action=ActionEnum.block)
        get_target_cache().targets=['test-model']
        gen = safety_guard.guard(dummy_generator)
        result = ''.join([s for s in gen(chat_history=[], model_id='test-model')[0]])
        expected_result = "<<<WARNING>>>msg<<</WARNING>>>"
        self.assertEqual(result, expected_result)
    
    def test_post_filter_warn(self):
        safety_guard = LlmSafetyGuard()
        safety_guard.detector = DummyDetector(post_filter_action=ActionEnum.warn)
        get_target_cache().targets=['test-model']
        gen = safety_guard.guard(dummy_generator)
        result = ''.join([s for s in gen(chat_history=[], model_id='test-model')[0]])
        expected_result = "在一疊舊照片中，<<<WARNING>>>msg<<</WARNING>>>有一張照片深深地烙印在我的記憶裡。這張照片的印象深刻的原因，是它記錄了我成長的過程、與他人的情景、環境的變遷和美麗的景色"
        self.assertEqual(result, expected_result)
    
    def test_post_filter_block(self):
        safety_guard = LlmSafetyGuard()
        safety_guard.detector = DummyDetector(post_filter_action=ActionEnum.block)
        get_target_cache().targets=['test-model']
        gen = safety_guard.guard(dummy_generator)
        result = ''.join([s for s in gen(chat_history=[], model_id='test-model')[0]])
        expected_result = "在一疊舊照片中，<<<WARNING>>>msg<<</WARNING>>>"
        self.assertEqual(result, expected_result)
    
    def test_post_filter_overwrite(self):
        safety_guard = LlmSafetyGuard()
        safety_guard.detector = DummyDetector(post_filter_action=ActionEnum.overwrite)
        get_target_cache().targets=['test-model']
        gen = safety_guard.guard(dummy_generator)
        result = ''.join([s for s in gen(chat_history=[], model_id='test-model')[0]])
        expected_result = "在一疊舊照片中，msgmsgmsg"
        self.assertEqual(result, expected_result)

if __name__ == '__main__':
    unittest.main()