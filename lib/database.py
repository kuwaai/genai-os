import json
import os
import logging
import inspect

from collections import OrderedDict
from contextlib import contextmanager
from sqlalchemy import create_engine, select
from sqlalchemy.orm import Session, sessionmaker

from model.base import Base
from model.rule import Rule, Target, ActionEnum
from model.detector import Detector, DetectorTypeEnum, ChainEnum
from model.embedding import Embedding
from serializer import AlchemyEncoder

logger = logging.getLogger(__name__)

class Database:
    def __init__(self, echo=False, autocommit=False, autoflush=False, pool_per_ping=True):
        conn_str = os.environ.get('DB_CONN', "postgresql+psycopg2://app:app@localhost/app")
        self.engine = create_engine(conn_str, echo=echo, pool_pre_ping=pool_per_ping)
        Base.metadata.create_all(self.engine)
        self.session = sessionmaker(autocommit=autocommit, autoflush=autoflush, bind=self.engine)

# Open a database session. Utilize the singleton pattern to ensure the database
# object is globally accessible.
@contextmanager
def open_db_session(db=Database()):
    try:
        session = db.session()
    except Exception:
        logger.exception('Failed to open the database session.')

    try:
        yield session
    finally:
        session.close()
        logger.debug('Database session closed.')

# Decorators for FastAPI endpoints
# This decorator will pass a database session as a parameter "db" to the target
# function.
def with_db_session(func):
    def wrap(*args, **kwargs):
        with open_db_session() as session:
            return func(db=session, *args, **kwargs)

    wrap = _remove_db_from_signature(func, wrap)
    return wrap

def awith_db_session(func):
    async def wrap(*args, **kwargs):
        with open_db_session() as session:
            return await func(db=session, *args, **kwargs)
    wrap = _remove_db_from_signature(func, wrap)
    return wrap

def _remove_db_from_signature(ref, target):
    
    # Correct the signature to compatible with pydantic.
    sig = inspect.signature(ref)
    params = sig.parameters.values()
    params = tuple(filter(lambda p: p.name != 'db', params))
    exposed_sig = sig.replace(parameters=params)
    target.__signature__ = exposed_sig
    return target

if __name__ == '__main__':
    logging.basicConfig(level=logging.DEBUG)

    embeddings = [
        Embedding(
            model='test-embedding-model',
            sentence='Example sentence1',
            embedding=[1.0, 2.0, 3.0, 4.0],
        ),
        Embedding(
            model='test-embedding-model',
            sentence='Example sentence2',
            embedding=[4.0, 3.0, 2.0, 1.0],
        )
    ]
    rule = Rule(
        name='test-rule',
        description='Test rule',
        action=ActionEnum.block,
        message='Test message',
        targets=[Target(model_id="test-model")],
        detectors=[
            Detector(
                type=DetectorTypeEnum.vector_guard,
                chain=ChainEnum.pre_filter,
                deny_list=[e.sentence for e in embeddings[:1]],
                embeddings=embeddings[:1]
            ),
            Detector(
                type=DetectorTypeEnum.vector_guard,
                chain=ChainEnum.post_filter,
                deny_list=[e.sentence for e in embeddings],
                embeddings=embeddings
            ),
            Detector(
                type=DetectorTypeEnum.keyword_guard,
                chain=ChainEnum.post_filter,
                deny_list=['Keyword']
            )
        ]
    )

    with open_db_session() as session:
        session.add_all([rule])
        session.commit()

        stmt = select(Rule)
        for rule in session.scalars(stmt):
            print(json.dumps(rule, cls=AlchemyEncoder, indent=2))
