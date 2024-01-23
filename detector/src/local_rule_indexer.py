import sys, os
import logging
import timeit
from sqlalchemy.orm import Session
from sqlalchemy import func

sys.path.append(os.path.join(os.path.dirname(__file__), '../../lib'))
from job import periodically_async_job
from database import awith_db_session
from model.rule import Rule, Target, ActionEnum
from model.detector import Detector, DetectorTypeEnum, ChainEnum
from model.embedding import Embedding

from .target_rules import TargetRules
from .local_storage import get_embedding_cache, get_guard_storage

logger = logging.getLogger(__name__)

class LocalRuleIndexer:
    @periodically_async_job(os.environ.get('LOCAL_RULE_INDEXING_PERIOD', 30.0))
    @awith_db_session
    async def update_local_rule(self, db: Session):
        
        logger.info("Start updating local rules")
        start_time = timeit.default_timer()

        # 1. Fetch all enabled rules from the database
        rules = db.query(Rule).filter(
            Rule.action != ActionEnum.none,
            Rule.targets.any(),
            Rule.detectors.any()
        ).all()
        
        logger.info(f"Activated rules: {len(rules)}")
        
        # 2. Fetch embeddings from the database. And update the local embedding cache.
        embeddings = db.query(Embedding).filter(Embedding.detectors.any()).all()
        cache = get_embedding_cache()
        for e in embeddings:
            cache.set(e.sentence, e.embedding)
        
        logger.info(f"Update the embedding cache with {len(embeddings)} entities.")

        # 3. Collect all rules of each target, since the detection is performed on a target-basis.
        target_rules_map = {}
        for r, t in [(r, t) for r in rules for t in r.targets]:
            for c in ChainEnum:
                key = tuple([t.model_id, c])
                if key not in target_rules_map:
                    target_rules_map[key] = []
                target_rules_map[key].append(r)
        

        # 4. Construct a MixedGuard and actions for each group of rules
        target_rules = [
            await TargetRules.from_rules(rules, c)
            for (_, c), rules in target_rules_map.items()
        ]
        
        
        # 5. Hot-swap every MixedGuard
        store = get_guard_storage()
        for (model_id, chain), val in zip(target_rules_map.keys(), target_rules):
            store.insert(model_id=model_id, chain=chain, target_rules=val)
        store.commit()

        logger.info(f'Local rules updated. It took {timeit.default_timer()-start_time:.0f} seconds.')
