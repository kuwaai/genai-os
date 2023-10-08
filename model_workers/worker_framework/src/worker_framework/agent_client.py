#!/bin/python3
# -#- coding: UTF-8 -*-

import logging
import requests
from urllib.parse import urljoin
import atexit
import asyncio
from worker_framework.metrics_manager import get_class_metrics

class AgentClient:
    """
    Agent client is responsible to communicate the control signal of the Agent.
    """


    def __init__(self, agent_endpoint: str, llm_name: str, public_endpoint: str, debug: bool = False):
        """
        Initialize the agent client.
        Arguments:
            agent_endpoint: The root endpoint of the Agent.
            llm_name: The name of this LLM.
            public_endpoint: The public endpoint URI that can be accessed by the Agent.
        """

        self.logger = logging.getLogger(__name__)
        if debug: self.logger.setLevel(logging.DEBUG)

        self.agent_endpoint = agent_endpoint
        self.llm_name = llm_name
        self.public_endpoint = public_endpoint

        self.metrics = get_class_metrics(self)
        self.metrics['registration'].info({
            'llm_name': self.llm_name,
            'public_endpoint': self.public_endpoint
        })
        self.metrics['state'].state('uninitialized')

    async def register(self, retry_cnt: int, backoff_time: int = 1):
        """
        Try to registration with the Agent.
        Arguments:
            retry_cnt: The rounds left to retry.
            backoff_time: If this round failed, how may seconds should wait before next round.
        
        Return:
            Return a boolean indicating whether successfully registered.
        """
        
        self.metrics['state'].state('trying')
        self.metrics['attempts'].inc()
        self.logger.info('Attempting registration with the Agent... {} times left.'.format(retry_cnt))
        try:
            def do_req():
                url = urljoin(self.agent_endpoint, './worker/register')
                data={
                    'name': self.llm_name,
                    'endpoint': self.public_endpoint
                }
                self.logger.debug('"POST {}", data={}'.format(url, data))
                return requests.post(url, json=data)
            event_loop = asyncio.get_event_loop()
            response = await event_loop.run_in_executor(None, do_req)
            if not response.ok : raise Exception
            else:
                self.logger.info('Registered.')
                atexit.register(self.unregister)
                self.metrics['state'].state('registered')
        except Exception as e:
            self.logger.warning('The server failed to register to Agent. Cause: {}.'.format(str(e)))
            
            if retry_cnt != 0:
                self.logger.info('Will retry registration after {} seconds.'.format(backoff_time))
                # Exponential backoff
                await asyncio.sleep(backoff_time)
                return await self.register(retry_cnt-1, backoff_time*2)
            
            else:
                self.metrics['state'].state('failed')
                return False

        return True

    def unregister(self):
        """
        Try to unregister with the Agent.

        """

        self.logger.info('Attempting to unregister with the Agent...')
        try:
            url = urljoin(self.agent_endpoint, './worker/unregister')
            data={
                'name': self.llm_name,
                'endpoint': self.public_endpoint
            }
            self.logger.debug('"POST {}", data={}'.format(url, data))
            response = requests.post(url, json=data)
            if not response.ok:
                self.logger.warning('Failed to unregister from Agent. Refused by Agent.')
            else:
                self.logger.info('Done.')
        except Exception as e:
            self.logger.warning('Failed to unregister from Agent. Cause: {}.'.format(str(e)))