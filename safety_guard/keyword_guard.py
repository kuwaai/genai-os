import math

from .guard_interface import GuardInterface
from ckip_transformers.nlp import CkipWordSegmenter

class KeywordGuard(GuardInterface):
  """
  Check whether the message contains specified keywords.
  """
  
  def __init__(self, ws_model="albert-tiny"):
    self.keywords:dict[int, set] = {}
    self.word_segmenter = CkipWordSegmenter(model=ws_model)

  def add_rule(self, rule_id: int, desc: str, black_list: [str], white_list: [str]=[]) -> bool:
    if rule_id in self.keywords: return False
    self.keywords[rule_id] = set(black_list)
    return True

  def score(self, records: [dict[str, str]]) -> dict[int, float]:
    result = {}
    text = records[-1]['msg']
    words = self.word_segmenter(input_text=[text], show_progress=False)[0]
    print(words)
    print(self.keywords)
    for rule_id, keywords in self.keywords.items():
      common = set(words).intersection(keywords)
      print(common)
      result[rule_id] = 0 if len(common) == 0 else 1
    result = dict(sorted(result.items()))
    return result

  def check(self, records: [dict[str, str]]) -> dict[int, bool]:
    score = self.score(records)
    result = {i: math.isclose(v, 1) for i, v in score.items()}
    
    return result
