#!/bin/python3
# -#- coding: UTF-8 -*-

from model_api_server.datatype import ChatRecord, Role
from model_api_server.interfaces import CompletionInterface
from typing import Generator

class ReflectModel(CompletionInterface):
  def complete(self, chat_history: [ChatRecord]) -> Generator[ChatRecord, None, None]:
    final_user_input = next(filter(lambda x: x.role == Role.USER, reversed(chat_history)))
    yield ChatRecord(final_user_input.msg, Role.BOT)