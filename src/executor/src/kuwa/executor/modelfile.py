import re
import json
import logging
from dataclasses import dataclass, field

logger = logging.getLogger(__name__)

def convert_value(value):
    precedence = [int, float]
    converted_v = None
    for target_type in precedence:
        try:
            converted_v = target_type(value)
            break
        except ValueError:
            pass
    if converted_v is None and value is not None:
        match value.lower():
            case "true":
                converted_v = True
            case "false":
                converted_v = False
            case "none":
                convert_v = None
            case _:
                converted_v = value
    return converted_v

class ParameterDict(dict):
    def __missing__(self, key):
        """
        Return a sub-dictionary which has common-prefix in key if not exact match.
        """
        prefix_dict = {k[len(key):]: v for k, v in self.items() if k.startswith(key)}
        return prefix_dict

@dataclass
class Modelfile:
    override_system_prompt:str=None
    messages:list[dict]=field(default_factory=list)
    template:str=None
    before_prompt:str=None
    after_prompt:str=None
    parameters:ParameterDict=field(default_factory=ParameterDict)

    @classmethod
    def from_json(cls, raw_modelfile:str):
        parsed = json.loads(raw_modelfile)
        if not parsed: parsed = []
        override_system_prompt = ""
        before_prompt = ''
        after_prompt = ''
        messages = []
        template = ""
        parameters = ParameterDict()
        for command in parsed:
            try:
                if command["name"] == "system":
                    override_system_prompt += command["args"]
                elif command["name"] == "message":
                    role, content = command["args"].split(' ', 1)
                    if role == "user":
                        messages += [{"content": content, "role": "user"}]
                    elif role == "assistant":
                        messages += [{"content": content, "role": "assistant"}]
                    elif role == "system":
                        override_system_prompt += content
                    else:
                        logger.debug(f"{role} doesn't existed!!")
                elif command['name'] == "template":
                    template = command['args']
                elif command['name'] == "before-prompt":
                    before_prompt += command['args']
                elif command['name'] == "after-prompt":
                    after_prompt += command['args']
                elif command["name"] == "parameter" or command["name"] == "kuwaparam":
                    args = re.sub(r'#.*$', '', command['args']).strip()
                    key, value = args.split(' ', 1)
                    parameters[key] = convert_value(value)
            except Exception as e:
                logger.exception(f"Error in modelfile `{command}` with error: `{e}`")
        return cls(
            override_system_prompt=override_system_prompt,
            messages=messages,
            template=template, 
            before_prompt=before_prompt,
            after_prompt=after_prompt,
            parameters=parameters
        )

    def __init__(self, **kwargs):
        for key, value in kwargs.items():
            setattr(self, key, value)