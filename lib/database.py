import json
import os
import logging

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
    def __init__(self, echo=True, autocommit=False, autoflush=False):
        conn_str = os.environ.get('DB_CONN', "postgresql+psycopg2://app:app@localhost/app")
        self.engine = create_engine(conn_str, echo=echo)
        Base.metadata.create_all(self.engine)
        self.session = sessionmaker(autocommit=autocommit, autoflush=autoflush, bind=self.engine)

# Open a database session. Utilize the singleton pattern to ensure the database
# object is globally accessible.
@contextmanager
def open_db_session(db=Database()):
    try:
        session = db.session()
        yield session
    except Exception:
        logger.exception('Failed to open the database session.')
    finally:
        session.close()
        logger.debug('Database session closed.')

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
                chain=ChainEnum.pre_filter,
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
        # session.add_all([rule])
        # session.commit()

        stmt = select(Rule)
        for rule in session.scalars(stmt):
            print(json.dumps(rule, cls=AlchemyEncoder, indent=2))
