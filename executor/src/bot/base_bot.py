from typing import Optional, List
from dataclasses import dataclass,  field
from abc import abstractmethod, ABC
from enum import Enum

class BotTypeEnum(Enum):
    SERVER='server'
    PROMPT='prompt'
    SCRIPT='script'
    PLUGIN='plugin'

@dataclass(kw_only=True)
class BaseBot:
    id: str
    created_at: int
    name: str
    description: Optional[str] = None
    type: BotTypeEnum