from typing import Optional, List, Generator
from dataclasses import dataclass,  field
from worker_framework.datatype import ChatRecord, Role
from worker_framework.interfaces import GeneralProcessInterface

from .base_bot import BaseBot

@dataclass(kw_only=True)
class PromptBot(BaseBot, GeneralProcessInterface):
    model: str
    instruction: str
    knowledge: Optional[List[str]] = field(default_factory=list)

    async def process(self, user_input: [ChatRecord], **kwargs) -> Generator[str, None, None]:
        print(user_input, kwargs)
        yield ''
        pass

    async def abort(self):
        raise NotImplementedError()