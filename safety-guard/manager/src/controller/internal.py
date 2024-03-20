import os
import sys
from fastapi import APIRouter

sys.path.append(os.path.join(os.path.dirname(__file__), '../../../lib'))
from database import open_db_session
from model.rule import Target

internal_router = APIRouter(prefix="/v1/internal")

# API endpoint for getting all guarded targets.
@internal_router.get("/targets", response_model=list)
def get_targets():
    targets = []
    with open_db_session() as db:
        targets = db.query(Target.model_id).all()
        targets = [t[0] for t in targets]
    return targets