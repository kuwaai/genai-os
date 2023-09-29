#!/bin/python3
# -#- coding: UTF-8 -*-

from model_api_server.datatype import ChatRecord, Role
from model_api_server.interfaces import GeneralProcessInterface
from typing import Generator
import time

class DocumentQaProcess(GeneralProcessInterface):
  def __init__(self):
    pass

  def process(self, user_input: [ChatRecord]) -> Generator[str, None, None]:
    
    final_user_input = next(filter(lambda x: x.role == Role.USER, reversed(user_input)))
    for t in final_user_input.msg:
      time.sleep(self.delay)
      yield t