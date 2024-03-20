from sqlalchemy import (
    String, Table, Column,
    ForeignKey, ForeignKeyConstraint
)
from .base import Base

rule_target_table = Table(
    "rule_targets",
    Base.metadata,
    Column("rule_id", ForeignKey("rules.id")),
    Column("target_model_id", ForeignKey("targets.model_id")),
)

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