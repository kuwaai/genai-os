from sqlalchemy.orm import Mapped, mapped_column, relationship
from sqlalchemy import (
    String, ARRAY, Enum, 
    Table, Column, ForeignKey, ForeignKeyConstraint
)
from typing import List
import enum

from .base import Base
from .embedding import Embedding

class DetectorTypeEnum(str, enum.Enum):
    keyword_guard = 'keyword-guard'
    vector_guard = 'vector-guard'
    llama_guard = 'llama-guard'
    charset_guard = 'charset-guard'

class ChainEnum(str, enum.Enum):
    pre_filter = 'pre-filter'
    post_filter = 'post-filter'

detector_embedding_table = Table(
    "detector_embeddings",
    Base.metadata,
    Column("detector_id", ForeignKey("detectors.id")),
    Column("embedding_sentence", String, nullable=False),
    Column("embedding_model", String, nullable=False),
    ForeignKeyConstraint(
        ["embedding_sentence", "embedding_model"],
        ["embeddings.sentence", "embeddings.model"]
    )
)

class Detector(Base):
    __tablename__ = "detectors"
    id:Mapped[int] = mapped_column(primary_key=True)
    rule_id:Mapped[int] = mapped_column(ForeignKey("rules.id"))
    type:Mapped[DetectorTypeEnum] = mapped_column(Enum(DetectorTypeEnum))
    chain:Mapped[ChainEnum] = mapped_column(Enum(ChainEnum))

    deny_list:Mapped[List[str]] = mapped_column(ARRAY(item_type=String))
    allow_list:Mapped[List[str]] = mapped_column(ARRAY(item_type=String), nullable=True)

    rule:Mapped["Rule"] = relationship(back_populates='detectors')
    embeddings:Mapped[List["Embedding"]] = relationship(secondary=detector_embedding_table)