from __future__ import annotations

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

def extract_text_from_quotes(text):
    """
    Extracts text from a string enclosed in single ('), double ("), or triple (''' \""") quotes, 
    handling escaped quotes and nested quotes of different types.

    Args:
        text: The input string.

    Returns:
        The extracted text without the surrounding quotes, or None if no quoted text is found.
    """

    match = re.search(r"""
        # Match single, double, or triple quotes 
        ^(\"\"\"|\'|\"|)
        # Capture the text inside the quotes (non-greedy)
        (.*?)
        # Match the same type of quote from the beginning
        \1$
    """, text, re.DOTALL | re.VERBOSE)

    if match:
        return match.group(2)
    else:
        return text

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

    @staticmethod
    def append_command(name, args, modelfile:Modelfile):
        single_arg_cmd = ("system", "template", "before-prompt", "after-prompt")
        if name in single_arg_cmd:
            args = extract_text_from_quotes(args)

        match name:
            case "template": modelfile.template = args
            case "system": modelfile.override_system_prompt += args
            case "before-prompt": modelfile.before_prompt += args
            case "after-prompt": modelfile.after_prompt += args

            case "message":
                role, content = [extract_text_from_quotes(x) for x in args.split(' ', 1)]
                if role in ["user", "assistant"]:
                    modelfile.messages += [{"content": content, "role": role}]
                elif role == "system":
                    modelfile.override_system_prompt += content
                else:
                    logger.debug(f"{role} doesn't existed!!")
            
            case "parameter" | "kuwaparam":
                key, value = [extract_text_from_quotes(x) for x in args.split(' ', 1)]
                modelfile.parameters[key] = convert_value(value)

            case _:
                raise ValueError(f'Unknown command "{name}"')

        return modelfile

    @classmethod
    def from_json(cls, raw_modelfile:str):
        raw_modelfile = json.loads(raw_modelfile)
        if not raw_modelfile: raw_modelfile = []
        parsed_modelfile = cls(
            override_system_prompt='',
            before_prompt='',
            after_prompt='',
            messages=[],
            template='',
            parameters=ParameterDict()
        )

        for command in raw_modelfile:
            try:
                name = command["name"]
                args = command["args"]
                # Filter out comments
                comment_prefix = '#'
                if comment_prefix in name:
                    args = ''
                args = args.split(comment_prefix)[0]
                
                parsed_modelfile = Modelfile.append_command(name, args, parsed_modelfile)
            except Exception as e:
                logger.exception(f"Error in modelfile `{command}` with error: `{e}`")

        return parsed_modelfile

    def __init__(self, **kwargs):
        for key, value in kwargs.items():
            setattr(self, key, value)