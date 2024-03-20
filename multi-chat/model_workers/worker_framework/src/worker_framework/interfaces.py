#!/bin/python3
# -#- coding: UTF-8 -*-

from typing import Generator
import time
from worker_framework.datatype import ChatRecord

class CompletionInterface:
  def complete(self, msg: [ChatRecord]) -> Generator[ChatRecord, None, None]:
    pass

class TextLevelFilteringInterface:
  def filter(self, msg: [ChatRecord]) -> [ChatRecord]:
    pass

class GeneralProcessInterface:
    async def process(self, user_input: [ChatRecord]) -> Generator[str, None, None]:
      pass