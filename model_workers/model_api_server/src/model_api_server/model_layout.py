#!/bin/python3
# -#- coding: UTF-8 -*-

import logging
import yaml
import importlib
from functools import reduce
import sys, os
from typing import Generator

from model_api_server.datatype import ChatRecord, Role
from model_api_server.interfaces import CompletionInterface, TextLevelFilteringInterface, GeneralProcessInterface

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

    def __init__(self, layout_file: str, debug: bool = False):

        self.logger = logging.getLogger(__name__)
        if debug: self.logger.setLevel(logging.DEBUG)

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

        if layout.get('override-process') != None:
            self.override_process = read_function(layout['override-process'], GeneralProcessInterface)
            self.logger.info('Override process class: {}'.format(type(self.override_process).__name__))
        else:
            self.override_process = None
            self.llm = read_function(layout['llm'], CompletionInterface)
            self.ingress_filters  = [read_function(func, TextLevelFilteringInterface) for func in layout['ingress-filters']]
            self.egress_filters = [read_function(func, TextLevelFilteringInterface) for func in layout['egress-filters']]
        
            self.logger.info('LLM class: {}'.format(type(self.llm).__name__))

    @staticmethod
    def apply_filters(data: [ChatRecord], filters: list[TextLevelFilteringInterface]) -> [ChatRecord]:
        """
        Sequentially apply filters to the data
        Arguments:
            data: The original data to be processed.
            filters: The filters to be apply. They will be applied from first to last.
        """
        return reduce(lambda d, f: f.filter(d), filters, data)

    def is_busy(self):
        return self.busy

    async def process(self, user_input: [ChatRecord]) -> Generator[str, None, None]:
        """
        Core part of the Model API server.
        The processing flow:
        [User input]->[Pre-processing filters]->[LLM]->[Post-processing filters]->[Output]
        """

        try:
            if self.override_process != None:
                async for t in self.override_process.process(user_input):
                    yield t
            else:
                processed_input = self.apply_filters(user_input, self.ingress_filters)
                self.logger.debug('Processed input: {}'.format(processed_input))
                for output_token in self.llm.complete(processed_input):
                    self.logger.debug('Model output: {}'.format(output_token))
                    processed_output_token = self.apply_filters([output_token], self.egress_filters)
                    self.logger.debug('Processed output: {}'.format(processed_output_token))
                    for t in processed_output_token:
                        if t.role == Role.USER: continue
                        yield t.msg
        except Exception as e:
            self.logger.error(e)
        finally:
            self.busy = False
            self.logger.debug('Finished.')
    