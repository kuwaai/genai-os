import json
from dataclasses import dataclass, field

@dataclass
class Modelfile:
    override_system_prompt:str=None
    messages:list[dict]=field(default_factory=list)
    template:str=None
    before_prompt:str=None
    after_prompt:str=None

    @classmethod
    def from_json(cls, raw_modelfile:str):
        parsed = json.loads(raw_modelfile)
        if not parsed: parsed = []
        override_system_prompt = ""
        before_prompt = ''
        after_prompt = ''
        messages = []
        template = ""
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
            except Exception as e:
                logger.exception(f"Error in modelfile `{command}` with error: `{e}`")
        return cls(
            override_system_prompt=override_system_prompt,
            messages=messages,
            template=template, 
            before_prompt=before_prompt,
            after_prompt=after_prompt
        )

    def __init__(self, **kwargs):
        for key, value in kwargs.items():
            setattr(self, key, value)