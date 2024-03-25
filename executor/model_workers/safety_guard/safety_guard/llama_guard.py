from .guard_interface import GuardInterface

from transformers import AutoTokenizer, AutoModelForCausalLM
from typing import Any
import logging
import torch
import jinja2
import re
import math

RULE_TEMPLATE = jinja2.Environment().from_string("""
{% for principle in principles -%}
O{{loop.index}}: {{ principle['description'] }}
{% if principle['black_list']|length > 0 -%}
Should not
{%- endif %}
{% for rule in principle['black_list'] -%}
- {{ rule }}
{% endfor %}
{%- if principle['white_list']|length > 0 -%}
Can
{%- endif %}
{% for rule in principle['white_list'] -%}
- {{ rule }}
{% endfor %}
{% endfor %}
""")

CHAT_TEMPLATE = """
{% if messages|length % 2 == 0 %}
  {% set role = 'Agent' %}
{% else %}
  {% set role = 'User' %}
{% endif %}
{{ \"[INST] Task: Check if there is unsafe content in '\" + role + \"' messages in conversations according our safety policy with the below categories.

<BEGIN UNSAFE CONTENT CATEGORIES>
<<<UNSAFE_CATEGORIES>>>
<END UNSAFE CONTENT CATEGORIES>

<BEGIN CONVERSATION>

\" }}
{% for message in messages -%}
  {% if (message['role'] == 'user') != (loop.index0 % 2 == 0) -%}
    {{ raise_exception('Conversation roles must alternate user/assistant/user/assistant/...') }}
  {%- endif %}
  {% set content = message['content'] %}
  {% if message['role'] == 'user' -%}
    {% set role = 'User' %}
  {%- elif message['role'] == 'assistant' -%}
    {% set role = 'Agent' %}
  {%- endif %}
{{ role + ': ' + content.strip() + '\n\n' }}
{%- endfor %}
{{ \"<END CONVERSATION>

Provide your safety assessment for \" + role + \" in the above conversation:
- First line must read 'safe' or 'unsafe'.
- If unsafe, a second line must include a comma-separated list of violated categories. [/INST]\" }}
"""

logger = logging.getLogger(__name__)

class LlamaGuard(GuardInterface):
  """
  Check whether the message contains specified keywords.
  """
  
  def __init__(self, model="meta-llama/LlamaGuard-7b", device="cuda"):
    self.tokenizer = AutoTokenizer.from_pretrained(model)
    self.model = AutoModelForCausalLM.from_pretrained(
      model,
      torch_dtype=torch.float16,
      device_map=device
    )
    self.device = device
    self.rules = []

  def add_rule(self, rule_id: int, desc: str, black_list: [str], white_list: [str]=[]) -> bool:
    if rule_id in self.rules: return False
    self.rules.append({
      'rule_id': rule_id,
      'description': desc,
      'black_list': black_list,
      'white_list': white_list
    })
    
    # Update the chat template
    rule_prompt = RULE_TEMPLATE.render(principles=self.rules).strip()
    chat_template = CHAT_TEMPLATE.replace('<<<UNSAFE_CATEGORIES>>>', rule_prompt).strip()
    self.tokenizer.chat_template = chat_template
    return True

  def score(self, records: [dict[str, str]]) -> dict[int, float]:
    
    chat = [
      {
        'role': record['role'].replace('bot', 'assistant'),
        'content': record['msg']
      }
      for record in records
    ]

    input_ids = self.tokenizer.apply_chat_template(chat, return_tensors="pt").to(self.device)

    logger.debug(f'Prompt:\n{self.tokenizer.batch_decode(input_ids)[0]}')

    output = self.model.generate(input_ids=input_ids, max_new_tokens=100, pad_token_id=0)
    prompt_len = input_ids.shape[-1]
    output = self.tokenizer.decode(output[0][prompt_len:], skip_special_tokens=True)
    
    logger.debug(f'LlamaGuard Output: \n{output}')

    internal_rule_idx = [int(i) for i in re.findall(r'O(\d+)', output)]
    result = {
      rule['rule_id']: 1 if i+1 in internal_rule_idx else 0
      for i, rule in enumerate(self.rules)
    }
    result = dict(sorted(result.items()))
    return result

  def check(self, records: [dict[str, str]]) -> dict[int, dict[str, Any]]:
    score = self.score(records)
    result = {
      i: {
        'violate': math.isclose(v, 1),
      }
      for i, v in score.items()
    }
    
    return result
