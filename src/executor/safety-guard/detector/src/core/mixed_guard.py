import sys, os, importlib

from .guard_interface import GuardInterface
from typing import Any

sys.path.append(os.getcwd())

def import_class(name: str):
    """
    Import class from specified module
    """

    module_name, class_name = name.rsplit('.', 1)
    return getattr(importlib.import_module(module_name), class_name)

class MixedGuard(GuardInterface):

  @classmethod
  async def create(cls, principles: list):
    self = cls()
    self.guards = {}
    self.rule_guard_map = []
    for i, principle in enumerate(principles):
      guard_cl = principle['guard_class']
      if not guard_cl in self.guards:
        self.guards[guard_cl] = import_class(guard_cl)()
      await self.guards[guard_cl].add_rule(
        i,
        principle['description'],
        principle['black_list'],
        principle.get('white_list')
      )
      self.rule_guard_map.append(guard_cl)
    return self

  async def add_rule(self, rule_id: int, desc: str, black_list: [str], white_list: [str]=[]) -> bool:
    return True

  async def score(self, records: [dict[str, str]]) -> dict[int, float]:
    # [TODO] Parallel evaluation.
    result = {}
    for guard in self.guards.values():
      result.update(await guard.score(records))
    result = dict(sorted(result.items()))
    return result
  
  async def check(self, records: [dict[str, str]]) -> dict[int, dict[str, Any]]:
    result = {}
    for guard in self.guards.values():
      result.update(await guard.check(records))
    result = dict(sorted(result.items()))
    
    return result