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

  def __init__(self, principles: list):
    self.guards = {}
    self.rule_guard_map = []
    for i, principle in enumerate(principles):
      guard_cl = principle['guard_class']
      if not guard_cl in self.guards:
        self.guards[guard_cl] = import_class(guard_cl)()
      self.guards[guard_cl].add_rule(
        i,
        principle['description'],
        principle['black_list'],
        principle.get('white_list')
      )
      self.rule_guard_map.append(guard_cl)

  def add_rule(self, rule_id: int, desc: str, black_list: [str], white_list: [str]=[]) -> bool:
    return True

  def score(self, records: [dict[str, str]]) -> dict[int, float]:
    # [TODO] Parallel evaluation.
    result = {}
    for guard in self.guards.values():
      result.update(guard.score(records))
    result = dict(sorted(result.items()))
    return result
  
  def check(self, records: [dict[str, str]]) -> dict[int, dict[str, Any]]:
    result = {}
    for guard in self.guards.values():
      result.update(guard.check(records))
    result = dict(sorted(result.items()))
    
    return result