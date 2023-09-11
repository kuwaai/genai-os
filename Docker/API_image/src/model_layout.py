#!/bin/python3
# -#- coding: UTF-8 -*-

import logging
import asyncio
from functools import reduce

from models.completion_interface import CompletionInterface
from filters.text_level_filtering_interface import TextLevelFilteringInterface


class ModelLayout:
    """
    ModelLayout is responsible arranging the models and models.
    The processing flow:
    [User input]->[Pre-processing filters]->[LLM]->[Post-processing filters]->[Output]
    """

    def __init__(self, llm, pre_filters=[], post_filters=[]):

        self.logger = logging.getLogger(__name__)
        
        # Check whether the modules implements correct interface
        assert issubclass(type(llm), CompletionInterface)
        for f in pre_filters:  assert issubclass(type(f), TextLevelFilteringInterface)
        for f in post_filters: assert issubclass(type(f), TextLevelFilteringInterface)

        self.llm = llm
        self.pre_filters = pre_filters
        self.post_filters = post_filters

        # State variable to indicate whether the model is processing another request
        self.busy = False
        
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

    def process(self, data):
        """
        Core part of the Model API server.
        The processing flow:
        [User input]->[Pre-processing filters]->[LLM]->[Post-processing filters]->[Output]
        """

        try:
            data = self.apply_filters(data, self.pre_filters)
            for output_token in self.llm.complete(data):
                output_token = self.apply_filters(output_token, self.post_filters)
                yield output_token
        except Exception as e:
            self.logger.error(e)
        finally:
            self.busy = False
            self.logger.debug('Finished.')
    