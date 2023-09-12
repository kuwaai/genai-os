#!/bin/python3
# -#- coding: UTF-8 -*-

from typing import Generator

class TextLevelFilteringInterface:
  def filter(self, text: str) -> str:
    pass