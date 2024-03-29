from sqlalchemy.orm import Mapped, mapped_column, relationship
from sqlalchemy import (
    String, ARRAY, Enum, 
    Table, Column, ForeignKey, ForeignKeyConstraint
)
from typing import List
import enum

from .base import Base
from .embedding import Embedding
from .association_table import detector_embedding_table

class DetectorTypeEnum(str, enum.Enum):
    keyword_guard = 'keyword-guard'
    vector_guard = 'vector-guard'
    llama_guard = 'llama-guard'
    charset_guard = 'charset-guard'

class ChainEnum(str, enum.Enum):
    pre_filter = 'pre-filter'
    post_filter = 'post-filter'

class Detector(Base):
    __tablename__ = "detectors"
    id:Mapped[int] = mapped_column(primary_key=True)
    rule_id:Mapped[int] = mapped_column(ForeignKey("rules.id"))
    type:Mapped[DetectorTypeEnum] = mapped_column(Enum(DetectorTypeEnum))
    chain:Mapped[ChainEnum] = mapped_column(Enum(ChainEnum))

    deny_list:Mapped[List[str]] = mapped_column(ARRAY(item_type=String))
    allow_list:Mapped[List[str]] = mapped_column(ARRAY(item_type=String), nullable=True)

    rule:Mapped["Rule"] = relationship(back_populates='detectors')
    embeddings:Mapped[List["Embedding"]] = relationship(
        secondary=detector_embedding_table,
        back_populates='detectors'
    )