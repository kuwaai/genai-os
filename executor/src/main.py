import asyncio
import logging
from worker_framework.datatype import ChatRecord, Role
from worker_framework.interfaces import GeneralProcessInterface
from typing import Generator

from .bot import BotTypeEnum
from .hub_client import MockHubClient

logger = logging.getLogger(__name__)

class ExecutorProcess(GeneralProcessInterface):
  def __init__(self):
    self.hub_client = MockHubClient()
    self.running_bots = {}

  async def process(self, user_input: [ChatRecord], **kwargs) -> Generator[str, None, None]:
    if 'bot_id' not in kwargs or 'job_id' not in kwargs:
      return
    
    bot_id = kwargs['bot_id']
    job_id = kwargs['job_id']

    # Get bot configuration
    bot = await self.hub_client.fetch_bot(bot_id)
    if not bot:
      return

    self.running_bots[job_id] = bot
    async for output in bot.process(user_input=user_input, **kwargs):
      yield output
    self.running_bots.pop(job_id)

  async def abort(self, job_id:str):
    if job_id in self.running_bots:
      await self.running_bots[job_id].abort(job_id)
      print(f'Aborted job {job_id}')