#!/bin/python3
# -#- coding: UTF-8 -*-

from model_api_server.interfaces import CompletionInterface
from model_api_server.datatype import ChatRecord, Role
from typing import Generator

class ReflectModel(CompletionInterface):
  def complete(self, records: [ChatRecord]) -> Generator[ChatRecord, None, None]:
    for record in records:
      yield ChatRecord(record.msg, Role.BOT)