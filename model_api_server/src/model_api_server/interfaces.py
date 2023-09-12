#!/bin/python3
# -#- coding: UTF-8 -*-

from typing import Generator
import time

class CompletionInterface:
  def complete(self, text: str) -> Generator[str, None, None]:
    pass

class TextLevelFilteringInterface:
  def filter(self, text: str) -> str:
    pass

class DummyModel(CompletionInterface):
  """
  DummyModel for default 
  """
  def __init__(self, content="I'm a dummy model."):
    self.content = content

  def complete(self, text: str) -> Generator[str, None, None]:
    for i in self.content:
      time.sleep(0.02)
      yield i
