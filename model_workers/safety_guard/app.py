#!/bin/python3
# -#- coding: UTF-8 -*-

from worker_framework.interfaces import CompletionInterface
from worker_framework.datatype import ChatRecord, Role
from safety_guard.mixed_guard import MixedGuard
from typing import Generator

class SafetyGuard(CompletionInterface):
  def __init__(self, safe_msg:str, unsafe_msg:str, principles: list):

    self.safe_msg = safe_msg
    self.unsafe_msg = unsafe_msg
    self.principles = sorted(principles, key=lambda x: x['guard_class'])
    self.guard = MixedGuard(self.principles)

  def complete(self, records: [ChatRecord]) -> Generator[ChatRecord, None, None]:
    records = [{
      'role': str(i.role),
      'msg': i.msg
    } for i in records]

    result = self.guard.check(records).items()

    msg = f"{records[-1]['msg']}\n\n{self.safe_msg}"
    if any([v for k, v in result]):
      msg = f"{records[-1]['msg']}\n\n{self.unsafe_msg}"
      violated_rules = [self.principles[k]['description'] for k, v in result if v]
      msg += ''.join([f"\n  - {v}" for v in violated_rules])
    
    yield ChatRecord(msg, Role.BOT)