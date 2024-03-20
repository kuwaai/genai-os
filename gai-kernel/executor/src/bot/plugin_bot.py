from typing import Optional, List, Generator
from dataclasses import dataclass,  field
from worker_framework.datatype import ChatRecord, Role
from worker_framework.interfaces import GeneralProcessInterface

from .base_bot import BaseBot

@dataclass(kw_only=True)
class PluginBot(BaseBot, GeneralProcessInterface):

    async def process(self, user_input: [ChatRecord], **kwargs) -> Generator[str, None, None]:
        raise NotImplementedError()

    async def abort(self):
        raise NotImplementedError()