from sqlalchemy.orm import Mapped, mapped_column, relationship
from sqlalchemy import (
    Enum, func,
    Table, Column, ForeignKey
)
from datetime import datetime
from typing import List
import enum

from .base import Base

class ActionEnum(str, enum.Enum):
    none = 'none'
    warn = 'warn'
    block = 'block'
    rewrite = 'rewrite'

rule_target_table = Table(
    "rule_targets",
    Base.metadata,
    Column("rule_id", ForeignKey("rules.id")),
    Column("target_id", ForeignKey("targets.id")),
)

class Rule(Base):
    __tablename__ = "rules"
    id:Mapped[int] = mapped_column(primary_key=True)
    name:Mapped[str]
    description:Mapped[str] = mapped_column(nullable=True)
    update_at:Mapped[datetime] = mapped_column(insert_default=func.now(), onupdate=func.now())
    
    action:Mapped[ActionEnum] = mapped_column(Enum(ActionEnum))
    message:Mapped[str] = mapped_column(nullable=True)
    
    targets:Mapped[List["Target"]] = relationship(secondary=rule_target_table, back_populates='rule')
    detectors:Mapped[List["Detector"]] = relationship(back_populates='rule')

class Target(Base):
    __tablename__ = "targets"
    id:Mapped[int] = mapped_column(primary_key=True)
    model_id:Mapped[str] = mapped_column()
    user_id:Mapped[str] = mapped_column(nullable=True)

    rule:Mapped["Rule"] = relationship(secondary=rule_target_table, back_populates='targets')