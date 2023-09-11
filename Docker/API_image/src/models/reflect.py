#!/bin/python3
# -#- coding: UTF-8 -*-

from . import CompletionInterface
from typing import Generator
import time

class ReflectModel(CompletionInterface):
  def complete(self, text: str) -> Generator[str, None, None]:
    for i in text:
      time.sleep(0.02)
      yield i