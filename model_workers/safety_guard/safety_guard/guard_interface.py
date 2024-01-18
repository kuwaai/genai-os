from abc import ABC, abstractmethod
from typing import Any

class GuardInterface(ABC):

  @abstractmethod
  def add_rule(self, rule_id: int, desc: str, black_list: [str], white_list: [str]=[]) -> bool:
    """
    Add a rule to guard.
    Input:
    - rule_id: A unique number of the principle.
    - desc: A description of the rule.
    - black_list: Bad examples that the guard should block.
    - white_list [Optional]: Good examples that the guard should not block.
    Output:
    Whether the rule is added successfully.
    Possible failure reason: Duplicated rule_id
    """
    pass

  @abstractmethod
  def score(self, records: [dict[str, str]]) -> dict[int, float]:
    """
    Return {rule_id: score}
    """
    pass

  @abstractmethod
  def check(self, records: [dict[str, str]]) -> dict[int, dict[str, Any]]:
    """
    Return {
      rule_id: {
        'violate': bool,
        'detail': str [optional]
      }
    }
    """
    pass
