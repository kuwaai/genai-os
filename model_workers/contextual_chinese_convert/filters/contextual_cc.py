#!/bin/python3
# -#- coding: UTF-8 -*-

from model_api_server.interfaces import TextLevelFilteringInterface
import opencc
from ckip_transformers.nlp import CkipWordSegmenter
from functools import reduce
# import hanzidentifier
import cjieba

def is_code(text: str, code: str):
  iconv = lambda t, c: t.encode(c, errors='ignore').decode(c)
  return len(text) == len(iconv(text, code))

class JiebaWordSegmenter:
  def __init__(self):
    pass
  
  def __call__(self, input_text: [str], **kwargs) -> [[str]]:
    return list(map(cjieba.cut, input_text))

class ContextualCC(TextLevelFilteringInterface):
  def __init__(self, dst_region='tw'):
    if dst_region == 'tw':
      # self.is_dst_code = lambda t: not hanzidentifier.is_simplified(t)
      self.is_dst_code = lambda t: not is_code(t, 'gb2312')
      opencc_config = 's2twp.json'
      self.ws_driver = JiebaWordSegmenter()

    elif dst_region == 'cn':
      # self.is_dst_code = lambda t: not hanzidentifier.is_traditional(t)
      self.is_dst_code = lambda t: not is_code(t, 'big5')
      opencc_config = 'tw2sp.json'
      self.ws_driver = CkipWordSegmenter(model="albert-tiny")
      
    else:
      raise ValueError('Unsupported destination region.') 

    self.converter = opencc.OpenCC(opencc_config)

  def convert(self, text:str):
    """
    Convert the text only if it contains unrecognized charter 
    """
    if self.is_dst_code(text):
      return text
    else:
      return self.converter.convert(text)

  def filter(self, text: str) -> str:

    if self.is_dst_code(text): return text

    # The segmenter work better on Traditional Chinese
    words = self.ws_driver(input_text=[text], show_progress=False)[0]
    result = reduce(lambda sum, t: sum+self.convert(t), words, '')

    return result