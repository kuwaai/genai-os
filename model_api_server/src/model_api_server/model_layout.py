#!/bin/python3
# -#- coding: UTF-8 -*-

import logging
import yaml
import importlib
from functools import reduce
import sys, os

from model_api_server.interfaces import CompletionInterface, TextLevelFilteringInterface

# The modules may not in the default searching path.
# Thus we append current working directory to the module searching path.
sys.path.append(os.getcwd())

def import_class(name: str):
    """
    Import class from specified module
    """

    module_name, class_name = name.rsplit('.', 1)
    return getattr(importlib.import_module(module_name), class_name)

class ModelLayout:
    """
    ModelLayout is responsible arranging the models and models.
    The processing flow:
    [User input]->[Pre-processing filters]->[LLM]->[Post-processing filters]->[Output]
    """

    def __init__(self, layout_file):

        self.logger = logging.getLogger(__name__)

        self.read_layout(layout_file)
        
        # State variable to indicate whether the model is processing another request
        self.busy = False

    def read_layout(self, layout_file: str):

        def read_function(function, check_class=None):
            """
            Read the function defined in the dictionary and check the class
            """

            class_name = import_class(function['class'])
            args = dict(function['args'])
            if check_class != None: assert issubclass(class_name, check_class)
            return class_name(**args) if any(args) else class_name()

        layout = {}
        with open(layout_file, 'r') as f:
            layout = yaml.safe_load(f)
        
        assert layout['version'] == 1

        self.llm = read_function(layout['llm'], CompletionInterface)
        self.pre_filters  = [read_function(func, TextLevelFilteringInterface) for func in layout['pre-filters']]
        self.post_filters = [read_function(func, TextLevelFilteringInterface) for func in layout['post-filters']]
        
        self.logger.info('LLM Class: {}'.format(type(self.llm).__name__))

    @staticmethod
    def apply_filters(data: str, filters: list[TextLevelFilteringInterface]):
        """
        Sequentially apply filters to the data
        Arguments:
            data: The original data to be processed.
            filters: The filters to be apply. They will be applied from first to last.
        """
        return reduce(lambda d, f: f.filter(d), filters, data)

    def is_busy(self):
        return self.busy

    def process(self, user_input: str):
        """
        Core part of the Model API server.
        The processing flow:
        [User input]->[Pre-processing filters]->[LLM]->[Post-processing filters]->[Output]
        """

        try:
            user_input = self.apply_filters(user_input, self.pre_filters)
            for output_token in self.llm.complete(user_input):
                output_token = self.apply_filters(output_token, self.post_filters)
                yield output_token
        except Exception as e:
            self.logger.error(e)
        finally:
            self.busy = False
            self.logger.debug('Finished.')
    