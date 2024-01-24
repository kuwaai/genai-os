import os, sys
import math
import logging
import faiss
import numpy as np

sys.path.append(os.path.join(os.path.dirname(__file__), '../../lib'))
from embedding_model_adapter import InfinityEmbeddingClient

from ..local_storage import get_embedding_cache
from .guard_interface import GuardInterface
from typing import Any

logger = logging.getLogger(__name__)

embedding_model = InfinityEmbeddingClient(
    host=os.environ.get('EMBED_HOST'),
    model_name=os.environ.get('EMBED_MODEL'),
)
embedding_model_dimension = int(os.environ.get('EMBED_DIM'))

class VectorGuard(GuardInterface):
  """
  Detect through embedding search.
  """
  
  def __init__(self, threshold=0.4082):
    # threshold=0.4258, precision=0.9630, recall=0.8966, f1=0.9286
    # threshold=0.3825, precision=0.8382, recall=0.9828, f1=0.9048
    # threshold=0.4966, precision=0.9773, recall=0.7414, f1=0.8431
    # threshold=0.4082, precision=0.9153, recall=0.9310, f1=0.9231
    self.vector_db = {}
    self.threshold = threshold

  async def add_rule(self, rule_id: int, desc: str, black_list: [str], white_list: [str]=[]) -> bool:
    if rule_id in self.vector_db: return False

    cache = get_embedding_cache()

    embeddings = []
    for text in black_list:
      embed = cache.get(text)
      if not embed:
        logger.warning(f'{text} not found in the cache.')
        embed = (await embedding_model.aembed([text]))[0]
      embeddings.append(embed)
    # text_embedding_pairs = list(zip(black_list, embeddings))
    index = faiss.IndexFlatL2(embedding_model_dimension)
    index.add(np.array(embeddings))
    # db.from_embeddings(text_embedding_pairs)

    self.vector_db[rule_id] = (index, black_list)

    return True

  async def _score(self, records: [dict[str, str]]) -> dict[int, dict[str, Any]]:
    msg = records[-1]['msg']
    result = {}
    for rule_id, (index, doc_map) in self.vector_db.items():
      embeddings = await embedding_model.aembed([msg])
      # doc, score = db.vector_store.similarity_search_with_relevance_scores(msg, k=1)[0]
      distance, idx = index.search(np.array(embeddings), 1)
      score = VectorGuard._euclidean_relevance_score_fn(distance[0][0])
      result[rule_id] = {
        'score': max(0, score),
        'doc': doc_map[idx[0][0]]
      }
    return result
  
  async def score(self, records: [dict[str, str]]) -> dict[int, float]:
    result = await self._score(records)
    return {i: r['score'] for i,r in result.items()}

  async def check(self, records: [dict[str, str]]) -> dict[int, dict[str, Any]]:
    text = records[-1]['msg']
    result = await self._score(records)
    result = {
      i: {
        'violate': (r['score'] > self.threshold),
        'detail': f"與 \"{r['doc']}\" 相似 (score: {r['score']:.5f})"
      }
      for i, r in result.items()
    }
    
    return result

  @staticmethod
  def _euclidean_relevance_score_fn(distance: float) -> float:
    """Return a similarity score on a scale [0, 1]."""
    # From LangChain
    # The 'correct' relevance function
    # may differ depending on a few things, including:
    # - the distance / similarity metric used by the VectorStore
    # - the scale of your embeddings (OpenAI's are unit normed. Many
    #  others are not!)
    # - embedding dimensionality
    # - etc.
    # This function converts the euclidean norm of normalized embeddings
    # (0 is most similar, sqrt(2) most dissimilar)
    # to a similarity function (0 to 1)
    return 1.0 - distance / math.sqrt(2)
