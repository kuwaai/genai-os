from sqlalchemy.orm import Mapped, mapped_column, relationship
from sqlalchemy import ARRAY, Float
from typing import List

from .base import Base
from .association_table import detector_embedding_table

class Embedding(Base):
    __tablename__ = "embeddings"

    sentence:Mapped[str] = mapped_column(primary_key=True)
    model:Mapped[str] = mapped_column(primary_key=True)
    embedding:Mapped[List[float]] = mapped_column(ARRAY(item_type=Float))
    detectors:Mapped[List["Detector"]] = relationship(
        secondary=detector_embedding_table,
        back_populates='embeddings'
    )