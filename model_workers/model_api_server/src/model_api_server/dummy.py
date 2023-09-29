#!/bin/python3
# -#- coding: UTF-8 -*-

from model_api_server.datatype import ChatRecord, Role
from model_api_server.interfaces import CompletionInterface
from typing import Generator
import time

class DummyModel(CompletionInterface):
  """
  Dummy model for default layout.
  """
  def __init__(self, content="I'm a dummy model."):
    self.content = content

  def complete(self, msg: [ChatRecord]) -> Generator[ChatRecord, None, None]:
    for i in self.content:
      time.sleep(0.02)
      yield ChatRecord(i, Role.BOT)