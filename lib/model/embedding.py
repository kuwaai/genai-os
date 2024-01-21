from sqlalchemy.orm import Mapped, mapped_column
from sqlalchemy import ARRAY, Float
from typing import List

from .base import Base

class Embedding(Base):
    __tablename__ = "embeddings"

    sentence:Mapped[str] = mapped_column(primary_key=True)
    model:Mapped[str] = mapped_column(primary_key=True)
    embedding:Mapped[List[float]] = mapped_column(ARRAY(item_type=Float))