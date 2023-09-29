#!/bin/python3
# -#- coding: UTF-8 -*-

from model_api_server.datatype import ChatRecord, Role
from model_api_server.interfaces import TextLevelFilteringInterface
import opencc

class OpenCC(TextLevelFilteringInterface):
  def __init__(self):
    self.converter = opencc.OpenCC('s2twp.json')
  
  def filter(self, records: [ChatRecord]) -> [ChatRecord]:
    return [ChatRecord(self.converter.convert(r.msg), r.role) for r in records]