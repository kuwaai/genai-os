#!/bin/python3
# -#- coding: UTF-8 -*-

import logging
import requests
from urllib.parse import urljoin
import atexit
import time

class AgentClient:
    def __init__(self, agent_endpoint, llm_name, public_endpoint):
        """
        Initialize the agent client.
        Arguments:
            agent_endpoint: The root endpoint of the Agent.
            llm_name: The name of this LLM.
            public_endpoint: The public endpoint URI that can be accessed by the Agent.
        """

        self.agent_endpoint = agent_endpoint
        self.llm_name = llm_name
        self.public_endpoint = public_endpoint
        self.logger = logging.getLogger(__name__)
        self.logger.addHandler(logging.StreamHandler())

    def register(self, retry_cnt, backoff_time=1):
        """
        Try to registration with the Agent.
        Arguments:
            retry_cnt: The rounds left to retry.
            backoff_time: If this round failed, how may seconds should wait before next round.
        
        Return:
            Return a boolean indicating whether successfully registered.
        """
        
        self.logger.info('Attempting registration with the Agent... {} times left.'.format(retry_cnt))
        try:
            response = requests.post(
                urljoin(self.agent_endpoint, '/register'),
                data={
                    'name': self.llm_name,
                    'endpoint': self.public_endpoint
                    }
            )
            if response.text == 'Failed': raise Exception
            else:
                self.logger.info('Registered.')
                atexit.register(self.unregister)
        except Exception as e:
            self.logger.warning('The server failed to register to Agent. Cause: {}.'.format(str(e)))
            
            if retry_cnt != 0:
                self.logger.info('Will retry registration after {} seconds.'.format(backoff_time))
                # Exponential backoff
                time.sleep(backoff_time)
                return self.register(retry_cnt-1, backoff_time*2)
            
            else:
                return False

        return True

    def unregister(self):
        """
        Try to unregister with the Agent.

        """

        self.logger.info('Attempting to unregister with the Agent...')
        try:
            response = requests.post(
                urljoin(self.agent_endpoint, '/unregister'),
                data={
                    'name': self.llm_name,
                    'endpoint': self.public_endpoint
                    }
            )
            if response.text == 'Failed':
                self.logger.warning('Failed to unregister from Agent. Refused by Agent.')
        except Exception as e:
            self.logger.warning('Failed to unregister from Agent. Cause: {}.'.format(str(e)))