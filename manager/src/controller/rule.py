import os
import sys
import logging
from fastapi import APIRouter, HTTPException
from typing import List
from sqlalchemy.orm import Session

sys.path.append(os.path.join(os.path.dirname(__file__), '../../../lib'))
from database import open_db_session, awith_db_session
from model.rule import Rule as RuleDbModel
from model.rule import Target, ActionEnum
from model.detector import Detector, DetectorTypeEnum, ChainEnum
from model.embedding import Embedding
from ..rule_model_converter import RuleApiModel, RuleModelConverter

rule_router = APIRouter(prefix="/v1/management")

# API endpoint to create a new rule
@rule_router.post("/rule", response_model=dict)
@awith_db_session
async def create_rule(rule: RuleApiModel, db: Session):
    new_rule = await RuleModelConverter.api2db(rule)
    new_rule = db.merge(new_rule)
    db.add(new_rule)
    db.commit()
    db.refresh(new_rule)
    return {"id": new_rule.id}

# API endpoint to get all rules
@rule_router.get("/rule", response_model=List[RuleApiModel])
@awith_db_session
async def get_all_rules(db: Session):
    rules = []
    rules = db.query(RuleDbModel).order_by(RuleDbModel.id.asc()).all()
    rules = [RuleModelConverter.db2api(r) for r in rules]
    return rules

# API endpoint to get a rule by ID
@rule_router.get("/rule/{rule_id}", response_model=RuleApiModel)
@awith_db_session
async def get_rule(rule_id: int, db: Session):
    rule = db.query(RuleDbModel).filter(RuleDbModel.id == rule_id).first()
    if rule is None:
        raise HTTPException(status_code=404, detail="Rule not found")
    rule = RuleModelConverter.db2api(rule)
    return rule

# API endpoint to update a rule by ID
@rule_router.put("/rule/{rule_id}", response_model=dict)
@awith_db_session
async def update_rule(rule_id: int, rule: RuleApiModel, db: Session):
    orig_rule = db.query(RuleDbModel).filter(RuleDbModel.id == rule_id).first()
    if orig_rule is None:
        raise HTTPException(status_code=404, detail="Rule not found")

    req_t = rule.retrieval_timestamp
    orig_t = round(orig_rule.update_at.timestamp())
    if req_t is None or req_t < orig_t:
        raise HTTPException(
            status_code=409,
            detail="The rule changes after the \"retrieval-timestamp\". For consistency, please fetch the rule again."
        )
    
    new_rule = await RuleModelConverter.api2db(rule)
    new_rule.id = orig_rule.id
    new_rule = db.merge(new_rule)

    db.commit()
    return {}

# API endpoint to delete a rule by ID
@rule_router.delete("/rule/{rule_id}", response_model=None, status_code=204)
@awith_db_session
async def delete_rule(rule_id: int, db: Session):
    await _delete_rule(rule_id=rule_id, db=db)
    return None

# The internal deleting procedure.
# Return: Whether the deletion succeed.
async def _delete_rule(rule_id: int, db: Session):
    rule = db.query(RuleDbModel).filter(RuleDbModel.id == rule_id).first()
    if rule is None: return False

    db.delete(rule)

    # Delete orphan targets
    for target in rule.targets:
        other_rule = [r != rule for r in target.rules]
        if any(other_rule): continue
        db.delete(target)

    # Delete orphan embeddings
    for detector in [d for d in rule.detectors if d.type==DetectorTypeEnum.vector_guard]:
        for embedding in detector.embeddings:
            other_rule = [d.rule != rule for d in embedding.detectors]
            if any(other_rule): continue
            db.delete(embedding)

    db.commit()

    return True