import os
import sys
import unittest
sys.path.append(os.path.join(os.path.dirname(__file__), '../'))
from main import extract_arguments

class TestExtractArguments(unittest.TestCase):

    def test_empty_input(self):
        user_input = ""
        expected_arguments = ""
        expected_prompt = ""
        arguments, prompt = extract_arguments(user_input)
        self.assertEqual(arguments, expected_arguments)
        self.assertEqual(prompt, expected_prompt)

    def test_no_arguments(self):
        user_input = "This is a test message"
        expected_arguments = ""
        expected_prompt = "This is a test message"
        arguments, prompt = extract_arguments(user_input)
        self.assertEqual(arguments, expected_arguments)
        self.assertEqual(prompt, expected_prompt)

    def test_single_argument_line(self):
        user_input = "/arg -ss 00:01:00"
        expected_arguments = "-ss 00:01:00"
        expected_prompt = ""
        arguments, prompt = extract_arguments(user_input)
        self.assertEqual(arguments, expected_arguments)
        self.assertEqual(prompt, expected_prompt)

    def test_multiple_arguments_line(self):
        user_input = "/arg -ss 00:01:00 -to 00:02:00"
        expected_arguments = "-ss 00:01:00 -to 00:02:00"
        expected_prompt = ""
        arguments, prompt = extract_arguments(user_input)
        self.assertEqual(arguments, expected_arguments)
        self.assertEqual(prompt, expected_prompt)

    def test_arguments_and_message(self):
        user_input = "/arg -ss 00:01:00 -to 00:02:00\nThis is a test message"
        expected_arguments = "-ss 00:01:00 -to 00:02:00"
        expected_prompt = "This is a test message"
        arguments, prompt = extract_arguments(user_input)
        self.assertEqual(arguments, expected_arguments)
        self.assertEqual(prompt, expected_prompt)

    def test_multiple_lines_with_arguments(self):
        user_input = "/arg -ss 00:01:00\n/arg -to 00:02:00\nThis is a test message"
        expected_arguments = "-ss 00:01:00"
        expected_prompt = "/arg -to 00:02:00\nThis is a test message"
        arguments, prompt = extract_arguments(user_input)
        self.assertEqual(arguments, expected_arguments)
        self.assertEqual(prompt, expected_prompt)

if __name__ == '__main__':
    unittest.main()