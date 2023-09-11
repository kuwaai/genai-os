#!/bin/python3
# -#- coding: UTF-8 -*-

from typing import Generator

class CompletionInterface:
  def complete(self, text: str) -> Generator[str, None, None]:
    pass