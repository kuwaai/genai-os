#!/bin/python3
# -#- coding: UTF-8 -*-

from dataclasses import dataclass
from enum import Enum

class Role(Enum):
    USER = 'user'
    SYS  = 'system'
    BOT  = 'bot'

    def __str__(self):
        return str(self.value)

@dataclass
class ChatRecord:
  msg: str   # Message.
  role: Role # Who said this.