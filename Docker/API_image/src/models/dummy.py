#!/bin/python3
# -#- coding: UTF-8 -*-

from .completion_interface import CompletionInterface
from typing import Generator
import time

class DummyModel(CompletionInterface):
  def complete(self, text: str) -> Generator[str, None, None]:
    for i in 'The crisp morning air tickled my face as I stepped outside. The sun was just starting to rise, casting a warm orange glow over the cityscape. I took a deep breath in, relishing in the freshness of the morning. As I walked down the street, the sounds of cars and chatter filled my ears. I could see people starting to emerge from their homes, ready to start their day.':
      time.sleep(0.02)
      yield i