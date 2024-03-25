#!/bin/python3
# -#- coding: UTF-8 -*-

import logging
import yaml
import importlib
from functools import reduce
import sys, os
from typing import Generator
import prometheus_client
import time

from worker_framework.metrics_manager import get_class_metrics
from worker_framework.datatype import ChatRecord, Role
from worker_framework.interfaces import CompletionInterface, TextLevelFilteringInterface, GeneralProcessInterface

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
    ModelLayout is responsible arranging the models and filters.
    The default processing flow:
    [User input]->[Ingress filters]->[LLM]->[Egress filters]->[Output]
    This flow can be override with custom flow, e.g. LangChain, by configuration.
    """

    def __init__(self, layout_file: str, debug: bool = False):

        self.logger = logging.getLogger(__name__)
        if debug: self.logger.setLevel(logging.DEBUG)

        self.read_layout(layout_file)

        # State variable to indicate whether the model is processing another request
        self.busy = False

        self.metrics = get_class_metrics(self)
        self.metrics['config'].info({
            'default_layout': str(self.override_process == None),
            'main_class': type(self.override_process or self.llm).__name__
        })
        self.metrics['state'].state('idle')

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
    def _apply_filters(data: [ChatRecord], filters: list[TextLevelFilteringInterface]) -> [ChatRecord]:
        """
        Sequentially apply filters to the data
        Arguments:
            data: The original data to be processed.
            filters: The filters to be apply. They will be applied from first to last.
        """
        return reduce(lambda d, f: f.filter(d), filters, data)

    def is_busy(self):
        return self.busy

    async def default_process(self, chat_history: [ChatRecord]) -> Generator[str, None, None]:
        """
        The default processing flow:
        [User input]->[Ingress filters]->[LLM]->[Egress filters]->[Output]
        """

        processed_input = ModelLayout._apply_filters(chat_history, self.ingress_filters)
        self.logger.debug('Processed input: {}'.format(processed_input))
        for output_token in self.llm.complete(processed_input):
            self.logger.debug('Model output: {}'.format(output_token))
            processed_output_token = ModelLayout._apply_filters([output_token], self.egress_filters)
            self.logger.debug('Processed output: {}'.format(processed_output_token))
            for t in processed_output_token:
                if t.role == Role.USER: continue
                yield t.msg

    def _update_statistics(self, duration_sec: float, total_output_length: int):
        """
        Update the internal statistical metrics.
        """

        throughput = total_output_length/duration_sec
        self.metrics['process_time_seconds'].observe(duration_sec)
        self.metrics['output_length_charters'].observe(total_output_length)
        self.metrics['output_throughput_charters_per_second'].observe(throughput)

    async def process(self, chat_history: [ChatRecord]) -> Generator[str, None, None]:
        """
        Core part of the Model API server.
        """

        process = self.default_process
        if self.override_process != None:
            process = self.override_process.process

        total_output_length = 0
        start_time = 0
        self.busy = True
        self.metrics['state'].state('busy')
            
        try:
            start_time = time.time()
            result = process(chat_history)
            async for msg in result:
                total_output_length += len(msg)
                yield msg
            duration_sec = time.time() - start_time
            self._update_statistics(duration_sec, total_output_length)

        except Exception as e:
            self.logger.exception('Error occurs when processing model layout.')
            self.metrics['failed'].inc()
            yield 'Error occurred. Please consult support.'

        finally:
            self.busy = False
            self.metrics['state'].state('idle')
            self.logger.debug('Finished.')
    