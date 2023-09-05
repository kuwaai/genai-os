#!/bin/python3
# -#- coding: UTF-8 -*-

from typing import Generator

class CompletionInterface:
  def complete(input: str) -> Generator[str, None, None]:
    pass