#!/bin/python3
# -#- coding: UTF-8 -*-

from model_api_server.datatype import ChatRecord, Role
from model_api_server.interfaces import GeneralProcessInterface
from typing import Generator
import asyncio

class SlowReflectProcess(GeneralProcessInterface):
  def __init__(self, delay_sec=1):
    self.delay = delay_sec

  async def process(self, user_input: [ChatRecord]) -> Generator[str, None, None]:
    
    final_user_input = next(filter(lambda x: x.role == Role.USER, reversed(user_input)))
    for t in final_user_input.msg:
      await asyncio.sleep(self.delay)
      yield t