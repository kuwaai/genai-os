import json
from dataclasses import dataclass, field

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
        if value.lower() == "true":
            converted_v = True
        elif value.lower() == "false":
            converted_v = False
        else:
            converted_v = value
    return converted_v

@dataclass
class Modelfile:
    override_system_prompt:str=None
    messages:list[dict]=field(default_factory=list)
    template:str=None
    before_prompt:str=None
    after_prompt:str=None
    parameters:dict=field(default_factory=dict)

    @classmethod
    def from_json(cls, raw_modelfile:str):
        parsed = json.loads(raw_modelfile)
        if not parsed: parsed = []
        override_system_prompt = ""
        before_prompt = ''
        after_prompt = ''
        messages = []
        template = ""
        parameters = {}
        for command in parsed:
            try:
                if command["name"] == "system":
                    override_system_prompt += command["args"]
                elif command["name"] == "message":
                    role, content = command["args"].split(' ', 1)
                    if role == "user":
                        messages += [{"msg":content, "isbot":False}]
                    elif role == "assistant":
                        messages += [{"msg":content, "isbot":True}]
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
                elif command["name"] == "parameter":
                    key, value = command["args"].split(' ', 1)
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