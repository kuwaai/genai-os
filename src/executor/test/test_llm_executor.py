import unittest
import itertools
import logging
from kuwa.executor.llm_executor import (
    LLMExecutor,
    to_openai_chat_format,
    rectify_chat_history,
    extract_last_url
)

class TestToOpenAiChatFormat(unittest.TestCase):

    def test_alternative(self):
        kuwa_history = [
            {"isbot": False, "msg": "hello1"},
            {"isbot": True, "msg": "world1"},
            {"isbot": False, "msg": "hello2"},
            {"isbot": True, "msg": "world2"},
        ]
        openai_history = [
            {"role": "user", "content": "hello1"},
            {"role": "assistant", "content": "world1"},
            {"role": "user", "content": "hello2"},
            {"role": "assistant", "content": "world2"},
        ]
        self.assertEqual(to_openai_chat_format(kuwa_history), openai_history)

    def test_multiple_user(self):
        kuwa_history = [
            {"isbot": False, "msg": "hello1"},
            {"isbot": False, "msg": "world1"},
            {"isbot": False, "msg": "hello2"},
            {"isbot": True, "msg": "world2"},
        ]
        openai_history = [
            {"role": "user", "content": "hello1"},
            {"role": "user", "content": "world1"},
            {"role": "user", "content": "hello2"},
            {"role": "assistant", "content": "world2"},
        ]
        self.assertEqual(to_openai_chat_format(kuwa_history), openai_history)
    
    def test_multiple_assistant(self):
        kuwa_history = [
            {"isbot": False, "msg": "hello1"},
            {"isbot": True, "msg": "world1"},
            {"isbot": True, "msg": "hello2"},
            {"isbot": True, "msg": "world2"},
        ]
        openai_history = [
            {"role": "user", "content": "hello1"},
            {"role": "assistant", "content": "world1"},
            {"role": "assistant", "content": "hello2"},
            {"role": "assistant", "content": "world2"},
        ]
        self.assertEqual(to_openai_chat_format(kuwa_history), openai_history)

class TestRectifyChatHistory(unittest.TestCase):

    def test_alternative(self):
        original_history = [
            {"role": "user", "content": "hello1"},
            {"role": "assistant", "content": "world1"},
            {"role": "user", "content": "hello2"},
            {"role": "assistant", "content": "world2"},
        ]
        expected_history = [
            {"role": "user", "content": "hello1"},
            {"role": "assistant", "content": "world1"},
            {"role": "user", "content": "hello2"},
            {"role": "assistant", "content": "world2"},
        ]
        self.assertEqual(rectify_chat_history(original_history), expected_history)
    
    def test_assistant_first(self):
        original_history = [
            {"role": "assistant", "content": "world1"},
            {"role": "user", "content": "hello2"},
            {"role": "assistant", "content": "world2"},
        ]
        expected_history = [
            {"role": "user", "content": "hello2"},
            {"role": "assistant", "content": "world2"},
        ]
        self.assertEqual(rectify_chat_history(original_history), expected_history)

    def test_multiple_user(self):
        original_history = [
            {"role": "assistant", "content": "hello1"},
            {"role": "user", "content": "world1"},
            {"role": "user", "content": "hello2"},
            {"role": "assistant", "content": "world2"},
        ]
        expected_history = [
            {"role": "user", "content": "world1"},
            {"role": "user", "content": "hello2"},
            {"role": "assistant", "content": "world2"},
        ]
        self.assertEqual(rectify_chat_history(original_history), expected_history)

    def test_multiple_assistant(self):
        original_history = [
            {"role": "assistant", "content": "hello1"},
            {"role": "assistant", "content": "world1"},
            {"role": "user", "content": "hello2"},
            {"role": "assistant", "content": "world2"},
        ]
        expected_history = [
            {"role": "user", "content": "hello2"},
            {"role": "assistant", "content": "world2"},
        ]
        self.assertEqual(rectify_chat_history(original_history), expected_history)

class TestExtractLastUrl(unittest.TestCase):

    urls = [
        "http://www.example.com",
        "https://www.example.com",
        "http://www.example.com:8800",
        "https://www.example.com:8800",
        "https://www.example.com/a/b/c/d/e/f/g/h/i.html",
        "https://www.test.com?pageid=123&testid=1524",
        "https://www.test.com/%E6%B8%AC%E8%A9%A6",
        "https://www.test.com/%E6%B8%AC%E8%A9%A6?q=%E6%B8%AC%E8%A9%A6",
        "https://www.test.com/do.html#A",
        "https://www.test.com/do.html#%E6%B8%AC%E8%A9%A6"
    ]

    def test_standalone(self):
        chat_history = [
            {"role": "user", "content": "hello1"},
            {"role": "assistant", "content": "world1"},
            {"role": "user", "content": ""},
            {"role": "assistant", "content": "world2"},
            {"role": "user", "content": "hello2"},
        ]
        expected_history = [
            {"role": "user", "content": ""},
            {"role": "assistant", "content": "world2"},
            {"role": "user", "content": "hello2"},
        ]
        for test_url in self.urls:
            history = chat_history.copy()
            history[2]["content"] = test_url
            url, history = extract_last_url(history)
            self.assertEqual(url, test_url)
            self.assertEqual(history, expected_history)
    
    def test_separate(self):
        chat_history = [
            {"role": "user", "content": "hello1"},
            {"role": "assistant", "content": "world1"},
            {"role": "user", "content": ""},
            {"role": "assistant", "content": "world2"},
            {"role": "user", "content": "hello2"},
        ]
        expected_history = [
            {"role": "user", "content": ""},
            {"role": "assistant", "content": "world2"},
            {"role": "user", "content": "hello2"},
        ]

        msg = "This is a test message!"
        separator = [" ", "  ", "\n", "\n\n", " \n"]
        template = ["{msg}{sep}{url}","{url}{sep}{msg}"]
        for test_url, sep, tmpl in itertools.product(self.urls, separator, template):
            history = chat_history.copy()
            exp_history = expected_history.copy()
            test_msg = tmpl.format(msg=msg, url=test_url, sep=sep)
            history[2]["content"] = test_msg
            exp_history[0]["content"] = msg
            url, history = extract_last_url(history)
            self.assertEqual(url, test_url)
            self.assertEqual(history, exp_history)

if __name__ == '__main__':
    logging.basicConfig(level="DEBUG")
    unittest.main()