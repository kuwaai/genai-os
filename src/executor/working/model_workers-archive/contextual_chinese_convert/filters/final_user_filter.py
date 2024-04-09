#!/bin/python3
# -#- coding: UTF-8 -*-

from worker_framework.datatype import ChatRecord, Role
from worker_framework.interfaces import TextLevelFilteringInterface

class FinalUserFilter(TextLevelFilteringInterface):
  """
  Retrieve the final message from the user.
  """
  
  def filter(self, records: [ChatRecord]) -> [ChatRecord]:
    return [next(filter(lambda x: x.role == Role.USER, reversed(records)))]
