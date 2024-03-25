import os, sys
from typing import Optional, List, Generator
from dataclasses import dataclass,  field
from worker_framework.datatype import ChatRecord, Role
from worker_framework.interfaces import GeneralProcessInterface

sys.path.append(os.path.join(os.path.dirname(__file__), '../../lib'))
from kuwa_chat_api_client import KuwaChatApiClient
from .base_bot import BaseBot

@dataclass(kw_only=True)
class PromptBot(BaseBot, GeneralProcessInterface):
    model: str
    instruction: str
    knowledge: Optional[List[str]] = field(default_factory=list)

    async def process(self, user_input: [ChatRecord], **kwargs) -> Generator[str, None, None]:
        user_input = self.prepend_instruction(user_input, self.instruction)

        model_api = KuwaChatApiClient(api_token=os.environ['KUWA_TOKEN'])
        async for chunk in model_api.chat_completion(bot_id=self.model, messages=user_input):
            if hasattr(self, 'stop'):
                break
            yield chunk

    async def abort(self, job_id:str):
        self.stop = True

    def prepend_instruction(self, chat_history: [ChatRecord], instruction: str) -> [ChatRecord]:
        for record in reversed(chat_history):
            if record.role == Role.USER:
                record.msg = f"{instruction}\n{record.msg}"
        return chat_history