import os
import re
import inspect
import argparse
import json
import yaml
from pydoc import locate

def expose_function_parameter(
    function,
    parser: argparse.ArgumentParser = None,
    accepted_types: list = ["int", "float", "Optional[int]", "Optional[float]"],
    defaults: dict = {}
    ) -> dict:
    """
    Expose the parameters of a function to the argparse.
    Return a dictionary containing the parameter's default value.
    
    defaults: Override the default value of the parameter.
    """
    parameters = inspect.signature(function).parameters.values()
    accepted_parameters = (p for p in parameters if str(p.annotation) in accepted_types)
    # Iterate over the function's parameters
    for p in accepted_parameters:
        p_name = p.name
        p_type = locate(re.sub("Optional\[(.+)\]", "\\1", p.annotation))
        p_default = defaults.get(p_name, p.default)

        # Register command-line arguments and default value
        parser.add_argument(f'--{p_name}', default=None, type=p_type, help=f'(default: {p_default})')
        defaults[p_name] = p_default

    return defaults

def read_config(conf_path):
    """
    Read configuration in JSON or YAML format.
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