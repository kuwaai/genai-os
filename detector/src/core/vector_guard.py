import math
import logging
import ahocorasick

from .guard_interface import GuardInterface
from typing import Any
from .document_store import DocumentStore

logger = logging.getLogger(__name__)

class VectorGuard(GuardInterface):
  """
  Detect through embedding search.
  """
  
  def __init__(self, embedding_model='thenlper/gte-large-zh', chunk_size=512, threshold=0.4082):
    # threshold=0.4258, precision=0.9630, recall=0.8966, f1=0.9286
    # threshold=0.3825, precision=0.8382, recall=0.9828, f1=0.9048
    # threshold=0.4966, precision=0.9773, recall=0.7414, f1=0.8431
    # threshold=0.4082, precision=0.9153, recall=0.9310, f1=0.9231
    self.vector_db = {}
    self.embedding_model_name = embedding_model
    self.chunk_size = chunk_size
    self.threshold = threshold

  def split(self, data:str):
    n = self.chunk_size
    if data is None: return []
    return [data[i:i+n] for i in range(0, len(data), n)]

  def add_rule(self, rule_id: int, desc: str, black_list: [str], white_list: [str]=[]) -> bool:
    if rule_id in self.vector_db: return False

    db = DocumentStore()
    black_list = [chunk for i in black_list for chunk in self.split(i)]
    embeddings = db.embedding_model.embed_documents(black_list)
    text_embedding_pairs = list(zip(black_list, embeddings))
    db.from_embeddings(text_embedding_pairs)

    self.vector_db[rule_id] = db

    return True

  def _score(self, records: [dict[str, str]]) -> dict[int, dict[str, Any]]:
    msg = records[-1]['msg']
    result = {}
    for rule_id, db in self.vector_db.items():
      doc, score = db.vector_store.similarity_search_with_relevance_scores(msg, k=1)[0]
      result[rule_id] = {
        'score': max(0, score),
        'doc': doc.page_content
      }
    return result
  
  def score(self, records: [dict[str, str]]) -> dict[int, float]:
    result = self._score[records]
    return {i: r['score'] for i,r in result.items()}

  def check(self, records: [dict[str, str]]) -> dict[int, dict[str, Any]]:
    text = records[-1]['msg']
    result = self._score(records)
    result = {
      i: {
        'violate': (r['score'] > self.threshold),
        'detail': f"與 \"{r['doc']}\" 相似 (score: {r['score']:.5f})"
      }
      for i, r in result.items()
    }
    
    return result
