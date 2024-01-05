import math
import logging
import ahocorasick

from .guard_interface import GuardInterface
from ckip_transformers.nlp import CkipWordSegmenter
from typing import Any

logger = logging.getLogger(__name__)

class WordListCoder:
  """
  Encode a word list into a UTF-8 string to perform substring searching algorithm.
  """
  def __init__(self):
    self.unknown = '<UNK>'
    self.init_code = ' '
    self.code_book = {self.unknown: self.init_code}
    self.inv_code_book = {v: k for k, v in self.code_book.items()}

  def encode(self, word_list:[str], append_new_code=True) -> str:
    result = ''
    for w in word_list:
      if (not w in self.code_book) and append_new_code:
        code = chr(len(self.code_book) + ord(self.init_code))
        self.code_book[w] = code
        self.inv_code_book[code] = w
      result += self.code_book[w if w in self.code_book else self.unknown]
    return result
  
  def decode(self, encoded_str:str) -> [str]:
    result = [
      self.unknown if not c in self.inv_code_book else self.inv_code_book[c]
      for c in encoded_str
    ]
    return result


class KeywordGuard(GuardInterface):
  """
  Check whether the message contains specified keywords.
  Utilize a BERT-based chinese word segmenter to prevent false positive.
  Moreover, we lower the complexity of substring searching with the Aho-Corasick algorithm.
  """
  
  def __init__(self, ws_model="albert-tiny"):
    self.kw_filters:dict[int, ahocorasick.Automaton] = {}
    self.word_segmenter = CkipWordSegmenter(model=ws_model)
    self.coder = WordListCoder()

  def add_rule(self, rule_id: int, desc: str, black_list: [str], white_list: [str]=[]) -> bool:
    if rule_id in self.kw_filters: return False

    # Since we segment the input string into a list of words,
    # we need to perform the same operation on the keywords, too. 
    black_list = self.word_segmenter(input_text=list(set(black_list)), show_progress=False)

    # In order to utilize C-based implementation of substring searching algorithm,
    # we encode the word list of each keyword into a UTF-8 string.
    black_list = [self.coder.encode(words) for words in black_list]

    # Construct the keyword filter.
    # We utilize the Aho-Corasick algorithm to reduce the complexity.
    keyword_filter = ahocorasick.Automaton()
    for keyword in black_list:
      keyword_filter.add_word(keyword, keyword)
    keyword_filter.make_automaton()

    self.kw_filters[rule_id] = keyword_filter
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

    # Segment input string into words and encode them to a UTF-8 string.
    # A BERT-based word segmenter can reduce the false positive of keyword-based filters.
    # However, since we use a C-based substring searching algorithm, we need to encode
    # the words into a UTF-8 string.
    words = self.word_segmenter(input_text=[text], show_progress=False)[0]
    words = self.coder.encode(words, append_new_code=False)

    # Match each keyword filter and provide the matched keywords in the detail field.
    for rule_id, kw_filter in self.kw_filters.items():
      matched_keyword = [match for _, match in kw_filter.iter(words)]
      detected = len(matched_keyword)!=0
      result[rule_id] = {'violate': detected}
      if detected:
        matched_keyword = list(set(map(lambda w: ''.join(self.coder.decode(w)), matched_keyword)))
        detail = ', '.join(matched_keyword)
        result[rule_id]['detail'] = detail
    
    result = dict(sorted(result.items()))
    return result
