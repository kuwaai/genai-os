import sys, os
import unittest

from typing import Any

sys.path.append(os.path.join(os.path.dirname(__file__), '../../lib'))
sys.path.append(os.path.join(os.path.dirname(__file__), '..'))
from src.core.charset_guard import CharsetGuard, CharsetRange

class TestCharsetRange(unittest.IsolatedAsyncioTestCase):
    
    async def test_basic_charset_pattern(self):
        sc_tester = CharsetRange.from_pattern('gb2312')
        tc_tester = CharsetRange.from_pattern('big5')
        test_string = "精确模式"

        self.assertEqual(sc_tester.charset, 'gb2312')
        self.assertEqual(tc_tester.charset, 'big5')
        
        self.assertEqual(sc_tester.in_range(test_string), True)
        self.assertEqual(tc_tester.in_range(test_string), True) # "确"是big5中的次常用漢字
    
    async def test_advanced_charset_pattern(self):
        sc_tester = CharsetRange.from_pattern('gb2312')
        tc_tester = CharsetRange.from_pattern('big5[0x01, 0xc67e]') # Syntax: "{charset}([{range begin}, {range end}])"
        test_string = "精确模式"

        range_begin = int('0x01', 0)
        range_end = int('0xc67e', 0)
        self.assertEqual(sc_tester.charset, 'gb2312')
        self.assertEqual(tc_tester.charset, 'big5')
        self.assertEqual(tc_tester.range_begin, range_begin)
        self.assertEqual(tc_tester.range_end, range_end)
        
        self.assertEqual(sc_tester.in_range(test_string), True)
        self.assertEqual(tc_tester.in_range(test_string), False)
        self.assertEqual(tc_tester.in_range(range_begin.to_bytes(1, 'big').decode('big5')), True)
        self.assertEqual(tc_tester.in_range(range_end.to_bytes(2, 'big').decode('big5')), True)

class TestCharsetGuard(unittest.IsolatedAsyncioTestCase):

    async def test_tc_sc(self):
        guard = CharsetGuard()
        await guard.add_rule(0, '', ['gb2312'], ['big5[0x01, 0xc67e]'])
        self.assertEqual((await guard.check([{'role':'user', 'msg': '中文測試'}]))[0]['violate'], False)
        self.assertEqual((await guard.check([{'role':'user', 'msg': 'English test.'}]))[0]['violate'], False)
        self.assertEqual((await guard.check([{'role':'user', 'msg': '精确模式'}]))[0]['violate'], True)
        self.assertEqual((await guard.check([{'role':'user', 'msg': '精确模式'}]))[0]['message'], "精確模式")
        self.assertEqual((await guard.check([{'role':'user', 'msg': '中文测试'}]))[0]['violate'], True)
        self.assertEqual((await guard.check([{'role':'user', 'msg': '中文测试'}]))[0]['message'], "中文測試")
        self.assertEqual((await guard.check([{'role':'user', 'msg': '尊敬的 [學術機構名] 院長/主任 [或相等職務]，我们非常荣幸地向您请求安排一场学术交流会'}]))[0]['violate'], True)
        self.assertEqual((await guard.check([{'role':'user', 'msg': '尊敬的 [學術機構名] 院長/主任 [或相等職務]，我们非常荣幸地向您请求安排一场学术交流会'}]))[0]['message'], "尊敬的 [學術機構名] 院長/主任 [或相等職務]，我們非常榮幸地向您請求安排一場學術交流會")
    
if __name__ == '__main__':
    unittest.main()