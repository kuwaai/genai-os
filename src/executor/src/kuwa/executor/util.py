import os
import re
import inspect
import argparse
import typing
import types
import json
import yaml
from pydoc import locate
from typing import Callable

class DescriptionParser:
    def __call__(self, doc:str, name:str) -> str:
        """
        The parser to extract function parameter description from docstring.

        Arguments:
          doc: The full docstring of the function.
          name: The name of the parameter to extract.

        Return:
          The extracted description of the parameter.
          Return None to use default value
        """
        raise NotImplementedError("You should implement this method.")

def expose_function_parameter(
    function,
    parser: argparse.ArgumentParser = None,
    accepted_types: tuple = (int, float),
    defaults: dict = None,
    desc_parser: DescriptionParser | Callable[[str, str], str] = None
    ) -> dict:
    """
    Expose the parameters of a function to the argparse.
    Return a dictionary containing the parameter's default value.
    
    Arguments:
      parser: The ArgumentParser or argument group to expose the arguments.
      acceptable_types: A list of accepted types that should be exposed.
      defaults: Override the default value of the parameter.
      desc_parser: The description parser. See the definition of DescriptionParser.
    
    Return:
      The dictionary containing the name and the default value of parameters.
    """

    # Extract the type and default value of parameters from the function's
    # signature, considering all arguments in the union type to include optional
    # parameters.
    union_types = (typing.Union, types.UnionType)
    param_defaults = {
        k: v.default
        for k, v in inspect.signature(function).parameters.items()
        if v.default is not inspect.Parameter.empty
    }
    param_types = {
        k: typing.get_args(v) if typing.get_origin(v) in union_types else
           ((v if typing.get_origin(v) is None else typing.get_origin(v)), )
        for k, v in typing.get_type_hints(function).items()
    }

    # Filter the parameters by accepted types.
    filter_types = lambda x: tuple(set(x).intersection(set(accepted_types)))
    params = (
        {
            "name": name,
            "type": filter_types(param_types.get(name))[0],
            "default": param_defaults.get(name)
        }
        for name in param_types.keys()
        if len(filter_types(param_types.get(name))) > 0
    )

    # Register command-line arguments and expose default value
    defaults = {} if defaults is None else defaults.copy()
    docstring = inspect.getdoc(function)
    for p in params:
        defaults[p["name"]] = p["default"]
        if not parser: continue

        desc = "-"
        if desc_parser is not None:
            parsed_desc = desc_parser(doc=docstring, name=p["name"])
            desc = parsed_desc if parsed_desc is not None else desc

        parser.add_argument(
            f'--{p["name"]}', default=p["default"], type=p["type"], help=desc
        )

    return defaults

def read_config(conf_path):
    """
    Read configuration from a JSON or YAML formatted file.
    """
    data = None
    with open(conf_path, 'r') as f:
        try:
            extension = os.path.splitext(conf_path)[1]
        except IndexError:
            extension = None

        if extension in ['.yaml', '.yml']: data = yaml.safe_load(f)
        elif extension in ['.json']: data = json.load(f)
        else:
            raise RuntimeError(
                f"Unsupported generation config \"{conf_path}\".\n"+\
                f"Support YAML or JSON format."
            )
    return data

def merge_config(base: dict, top: dict) -> dict:
    """
    Merge two dictionary.

    Arguments:
      base: The base dictionary to override.
      top: Override the base value if have the same key and the new value is not None.
    
    Return:
      The merges dictionary.
    """
    base = base.copy()
    base.update((k, v) for k, v in top.items() if v is not None)
    return base