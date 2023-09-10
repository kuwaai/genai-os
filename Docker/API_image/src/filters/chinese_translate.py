#!/bin/python3
# -#- coding: UTF-8 -*-

from .text_level_filtering_interface import TextLevelFilteringInterface
import opencc

class OpenCC(TextLevelFilteringInterface):
  def __init__(self):
    self.converter = opencc.OpenCC('s2t.json')
  
  def filter(self, text: str) -> str:
    return self.converter.convert(text)