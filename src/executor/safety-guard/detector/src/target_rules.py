import os, sys
from dataclasses import dataclass, field
from typing import List, Dict, Tuple, Any

sys.path.append(os.path.join(os.path.dirname(__file__), '../../lib'))
from .core.guard_interface import GuardInterface
from .core.mixed_guard import MixedGuard
from model.detector import DetectorTypeEnum, ChainEnum
from model.rule import Rule, ActionEnum

@dataclass
class TargetRules:
    """
    A dataclass to store the rules of a target (model_id, chain).
    guard: The guard detector.
    actions: The action of each rules. Signature: {rule_id: {'action': ActionEnum, 'message': str)}
    """
    guard: GuardInterface = None
    actions: Dict[int, Dict[str, Any]] = field(default_factory = lambda: {})

    @classmethod
    async def from_rules(cls, rules: List[Rule], chain: ChainEnum):
        """
        Construct a TargetRules object from the list of Rules.
        Assume the Rules shares common target.
        """
        guard_rules, actions = TargetRules._rules_to_params(rules=rules, chain=chain)

        guard = await MixedGuard.create(guard_rules)

        result = cls(
            guard = guard,
            actions = actions
        )

        return result

    @staticmethod
    def _rules_to_params(rules: List[Rule], chain: ChainEnum) -> Tuple[List, Dict]:
        """
        Compose the parameters of the MixedGuard from given rules.
        """

        guard_class_map = {
            DetectorTypeEnum.keyword_guard: 'src.core.keyword_guard.KeywordGuard',
            DetectorTypeEnum.vector_guard:  'src.core.vector_guard.VectorGuard',
            DetectorTypeEnum.llama_guard:   'src.core.llama_guard.LlamaGuard',
            DetectorTypeEnum.charset_guard: 'src.core.charset_guard.CharsetGuard',
        }

        # Note that the rule_id here does not equals to the id field in the rule record.
        # Since the MixedGuard consumes a list of rules, we use the list index as pseudo rule_id.
        guard_rules = []
        actions = {}

        for r in rules:
            for d in r.detectors:
                if d.chain != chain: continue
                guard_rules.append(dict(
                    guard_class = guard_class_map[d.type],
                    description = '',
                    black_list = d.deny_list,
                    white_list = d.allow_list
                ))
                actions[len(actions)] = dict(action=r.action, message=r.message)

        return guard_rules, actions
