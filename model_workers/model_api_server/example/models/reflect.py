#!/bin/python3
# -#- coding: UTF-8 -*-

from model_api_server.datatype import ChatRecord, Role
from model_api_server.interfaces import CompletionInterface
from typing import Generator

class ReflectModel(CompletionInterface):
  def complete(self, msg: [ChatRecord]) -> Generator[ChatRecord, None, None]:
    yield ChatRecord(msg[-1].msg, Role.BOT)