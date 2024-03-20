import time
from abc import abstractmethod, ABC
from typing import Dict, Any, Optional

from .bot import BotTypeEnum, BaseBot, PromptBot

class HubClient(ABC):
    @abstractmethod
    async def fetch_bot(self, bot_id:str) -> BaseBot:
        pass

class MockHubClient(HubClient):

    store = {
        'test_prompt_bot': PromptBot(
            id="test_prompt_bot",
            created_at=int(time.time()),
            name="Test prompt-based bot.",
            type=BotTypeEnum.PROMPT,
            model="gemma-7b-instruct",
            instruction="你接下來將扮演一名正在學中文的美國大學生，名字叫Ted。做好角色扮演工作，否則將受到懲罰。"
        ),
        'test_rag_bot': PromptBot(
            id="test_rag_bot",
            created_at=int(time.time()),
            name="Test prompt-based bot with external knowledge.",
            type=BotTypeEnum.PROMPT,
            model="gemma-7b-instruct",
            instruction="請以以下內容為基礎，回答問題。\n\n{% for doc in docs %}{doc}\n\n{% endfor %}\n\n問題：",
            knowledge=[
                "kuwa-rag:https://www.example.com/article/1", # Single web page
                "kuwa-rag:google-search:", # Search
                "kuwa-rag:google-search:?site=www.example.com", # Site search
                "kuwa-rag:db:https://www.example.com/db.yaml", # Pre-built database 
            ]
        ),
    }

    async def fetch_bot(self, bot_id:str) -> BaseBot:
        return self.store.get(bot_id)