#!/bin/python3
# -#- coding: UTF-8 -*-

from worker_framework.interfaces import CompletionInterface
from worker_framework.datatype import ChatRecord, Role
from src.safety_guard.detector.mixed_guard import MixedGuard
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
    result = [(k, v) for k, v in result if v['violate']]

    msg = f"{records[-1]['msg']}\n\n{self.safe_msg}"
    if len(result) != 0:
      msg = f"{records[-1]['msg']}\n\n{self.unsafe_msg}"
      violated_rules = [self.format_violated_rules(k, v) for k, v in result]
      msg += ''.join([f"\n  - {v}" for v in violated_rules])
    
    yield ChatRecord(msg, Role.BOT)

  def format_violated_rules(self, index, check_result):
    if not check_result['violate']:
      return None
    result = self.principles[index]['description']
    if 'detail' in check_result:
      result += ': '+check_result['detail']
    return result