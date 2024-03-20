import sys, os
import unittest

from typing import Any

sys.path.append(os.path.join(os.path.dirname(__file__), '../../lib'))
sys.path.append(os.path.join(os.path.dirname(__file__), '..'))
from src.target_rules import TargetRules
from src.core.guard_interface import GuardInterface
from model.detector import Detector, DetectorTypeEnum, ChainEnum
from model.rule import Rule, Target, ActionEnum

class TestTargetRules(unittest.IsolatedAsyncioTestCase):
    
    rules = [
            # Rule(
            #     id=10,
            #     name='簡體偵測/轉繁',
            #     description='偵測到輸出簡體字就執行指定行為。若規則行為為「改寫」，則會進行簡繁轉換，且有轉換錯誤的風險。',
            #     action=ActionEnum.overwrite,
            #     message='',
            #     targets=[],
            #     detectors=[
            #         Detector(
            #             type=DetectorTypeEnum.charset_guard,
            #             chain=ChainEnum.post_filter,
            #             deny_list=[]
            #         )
            #     ]
            # ),
            Rule(
                id=9,
                name='LLaMA Guard',
                description='使用 LLaMA Guard 防護六大類有害問答(暴力或仇恨言論、露骨內容、預備犯罪、槍枝及非法武器、管制或受控物質、自我傷害)。',
                action=ActionEnum.block,
                message='系統檢測到不安全內容，相關內容違反我們的使用者政策，因此停止輸出模型內容。',
                targets=[],
                detectors=[
                    Detector(
                        type=DetectorTypeEnum.llama_guard,
                        chain=ChainEnum.pre_filter,
                        deny_list=[]
                    ),
                    Detector(
                        type=DetectorTypeEnum.llama_guard,
                        chain=ChainEnum.post_filter,
                        deny_list=[]
                    )
                ]
            ),
            Rule(
                id=8,
                name='Test Keyword Guard',
                description='Test keyword guard',
                action=ActionEnum.warn,
                message='Test message1',
                targets=[],
                detectors=[
                    Detector(
                        type=DetectorTypeEnum.keyword_guard,
                        chain=ChainEnum.pre_filter,
                        deny_list=['Test1', 'Test2']
                    ),
                    Detector(
                        type=DetectorTypeEnum.keyword_guard,
                        chain=ChainEnum.post_filter,
                        deny_list=['Test3', 'Test4']
                    )
                ]
            ),
            Rule(
                id=7,
                name='Test Vector Guard',
                description='Test vector guard',
                action=ActionEnum.block,
                message='Test message2',
                targets=[],
                detectors=[
                    Detector(
                        type=DetectorTypeEnum.vector_guard,
                        chain=ChainEnum.pre_filter,
                        deny_list=['Test text1', 'Test text2']
                    )
                ]
            )
        ]
    
    async def test_from_rules(self):
        
        pre_filter_params = TargetRules._rules_to_params(rules=self.rules, chain=ChainEnum.pre_filter)
        post_filter_params = TargetRules._rules_to_params(rules=self.rules, chain=ChainEnum.post_filter)

        expected_pre_filter_params = (
            [
                {
                    'guard_class': 'src.core.llama_guard.LlamaGuard',
                    'description': '',
                    'black_list': [],
                    'white_list': None,
                },
                {
                    'guard_class': 'src.core.keyword_guard.KeywordGuard',
                    'description': '',
                    'black_list': ['Test1', 'Test2'],
                    'white_list': None,
                },
                {
                    'guard_class': 'src.core.vector_guard.VectorGuard',
                    'description': '',
                    'black_list': ['Test text1', 'Test text2'],
                    'white_list': None,
                },
            ],
            {
                0: {'action': ActionEnum.block, 'message': '系統檢測到不安全內容，相關內容違反我們的使用者政策，因此停止輸出模型內容。'},
                1: {'action': ActionEnum.warn, 'message': 'Test message1'},
                2: {'action': ActionEnum.block, 'message': 'Test message2'},
            }
        )

        expected_post_filter_params = (
            [
                {
                    'guard_class': 'src.core.llama_guard.LlamaGuard',
                    'description': '',
                    'black_list': [],
                    'white_list': None,
                },
                {
                    'guard_class': 'src.core.keyword_guard.KeywordGuard',
                    'description': '',
                    'black_list': ['Test3', 'Test4'],
                    'white_list': None,
                },
            ],
            {
                0: {'action': ActionEnum.block, 'message': '系統檢測到不安全內容，相關內容違反我們的使用者政策，因此停止輸出模型內容。'},
                1: {'action': ActionEnum.warn, 'message': 'Test message1'},
            }
        )

        self.maxDiff = None
        self.assertEqual(pre_filter_params, expected_pre_filter_params)
        self.assertEqual(post_filter_params, expected_post_filter_params)
    
    async def test_instantiating(self):
        target_result = await TargetRules.from_rules(rules=self.rules, chain=ChainEnum.pre_filter)

if __name__ == '__main__':
    unittest.main()