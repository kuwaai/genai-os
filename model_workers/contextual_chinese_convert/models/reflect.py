#!/bin/python3
# -#- coding: UTF-8 -*-

from model_api_server.interfaces import CompletionInterface
from typing import Generator

class ReflectModel(CompletionInterface):
  def complete(self, text: str) -> Generator[str, None, None]:
    yield text