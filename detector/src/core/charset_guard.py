import sys
import re
import math
import logging

from .guard_interface import GuardInterface
from typing import Any, Dict, List
from functools import reduce
from itertools import product

logger = logging.getLogger(__name__)

class CharsetRange:
  """
  A charset detector with specified range.
  """
  
  def __init__(self, charset: str, range_begin=0, range_end=sys.maxsize):
    self.charset = charset
    self.range_begin = range_begin
    self.range_end = range_end
  
  @classmethod
  def from_pattern(cls, charset_pattern: str):
    pattern = r'^(?P<charset>[a-zA-Z0-9_-]+)(\[(?P<range_begin>[xa-fA-F0-9]+),\s*(?P<range_end>[xa-fA-F0-9]+)\])?$'
    match = re.match(pattern, charset_pattern)

    charset = match.group('charset')
    range_begin = match.group('range_begin') or str(0)
    range_end = match.group('range_end') or str(sys.maxsize)

    return cls(charset, int(range_begin, 0), int(range_end, 0))
  
  def in_range(self, text:str):
    """
    Detect whether the given text can be encoded to specified charset.
    """

    codes = [int.from_bytes(t.encode(self.charset, errors='ignore'), byteorder="big") for t in text]
    return all([c != 0 and self.range_begin <= c <= self.range_end for c in codes])

class CharsetGuard(GuardInterface):
  """
  Detect whether the last message contains charters in the specified charset.
  """
  
  def __init__(self):
    self.rules = {}

  async def add_rule(self, rule_id: int, desc: str, black_list: [str], white_list: [str]=[]) -> bool:
    if rule_id in self.rules: return False
    
    self.rules[rule_id] = {
      'black_list': [CharsetRange.from_pattern(i) for i in black_list],
      'white_list': [CharsetRange.from_pattern(i) for i in white_list]
    }
    return True
    
  async def score(self, records: [dict[str, str]]) -> dict[int, float]:
    check_result = self.check(records)
    result = {
      i: 1 if v['violate'] else 0
      for i, v in check_result.items()
    }
    return result

  async def check(self, records: [dict[str, str]]) -> dict[int, dict[str, Any]]:
    result = {}
    text = records[-1]['msg']

    for rule_id, rule in self.rules.items():
      in_black_list = any([c.in_range(text) for c in rule['black_list']])
      in_white_list = any([c.in_range(text) for c in rule['white_list']])
      violate = in_black_list and not in_white_list
      result[rule_id] = {'violate': violate}
      if not violate: continue

      src_charsets = [c.charset for c in rule['black_list']]
      dst_charsets = [c.charset for c in rule['white_list']]
      for src, dst in set(product(src_charsets, dst_charsets)):
        converter_code = f'{src}->{dst}'
        if converter_code not in CONVERTER_REGISTRY: continue
        converter = CONVERTER_REGISTRY[converter_code]
        text = await converter.convert(text)

      result[rule_id]['message'] = text

    return result

# ====== Converter ======
import opencc
# import jieba_fast as jieba

class Converter:
  codes:[str] = []

  async def convert(self, text:str)->str:
    raise NotImplementedError()

CONVERTER_REGISTRY:Dict[str, Converter] = {}
def register_converter(cls):
  assert issubclass(cls, Converter)
  for code in cls.codes:
    CONVERTER_REGISTRY[code] = cls()

  return cls

@register_converter
class Sc2TcConverter(Converter):
  codes:[str] = ['gb2312->big5']

  def __init__(self):
    self.converter = opencc.OpenCC('s2twp.json')
  
  async def convert(self, text:str)->str:
    # Word segmenting using jieba will block the thread. Thus, we direct convert the
    # input text with OpenCC.
    # words = jieba.cutl(text, HMM=False)
    # words = map(self.converter.convert, words)
    # return ''.join(words)
    return self.converter.convert(text)
