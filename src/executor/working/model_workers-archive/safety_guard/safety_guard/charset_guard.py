import math
import logging

from .guard_interface import GuardInterface
from typing import Any
from functools import reduce

logger = logging.getLogger(__name__)

def is_code(text: str, code: str):
  """
  Detect whether the given text can be encoded to specified code.
  """

  iconv = lambda t, c: t.encode(c, errors='ignore').decode(c)
  return len(text) == len(iconv(text, code))

def in_code_list(text: str, code_list: [str]):
  """
  Detect whether the given text can be encoded in one of the code list.
  """

  result = reduce(
    lambda sum, code: sum or is_code(text, code),
    code_list,
    False
  )
  return result

class CharsetGuard(GuardInterface):
  """
  Detect whether the last message contains some charter in the specified charset.
  """
  
  def __init__(self):
    self.rules = {}

  def add_rule(self, rule_id: int, desc: str, black_list: [str], white_list: [str]=[]) -> bool:
    if rule_id in self.rules: return False
    
    self.rules[rule_id] = {
      'black_list': black_list,
      'white_list': white_list
    }
    return True
    
  def score(self, records: [dict[str, str]]) -> dict[int, float]:
    check_result = self.check(records)
    result = {
      i: 1 if v['violate'] else 0
      for i, v in check_result.items()
    }
    return result

  def check(self, records: [dict[str, str]]) -> dict[int, dict[str, Any]]:
    result = {}
    text = records[-1]['msg']

    for rule_id, rule in self.rules.items():
      in_black_list = in_code_list(text, rule['black_list'])
      in_white_list = in_code_list(text, rule['white_list'])
      violate = in_black_list and not in_white_list
      result[rule_id] = {
        'violate': violate
      }

    return result