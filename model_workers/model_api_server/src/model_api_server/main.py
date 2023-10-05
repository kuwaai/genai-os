#!/bin/python3
# -#- coding: UTF-8 -*-

import sys, os
import logging, yaml
import asyncio

from model_api_server.config import Config
from model_api_server.agent_client import AgentClient
from model_api_server.model_layout import ModelLayout
from model_api_server.api_server import ModelApiServer

class RegistrationJob:
    """
    The background job to register with the Agent on startup
    Reference: https://github.com/tiangolo/fastapi/issues/2713#issuecomment-768949823
    """
    
    def __init__(self, agent_client, retry_count: int, ignore_agent: bool):
        self.agent_client = agent_client
        self.retry_count = retry_count
        self.ignore_agent = ignore_agent

    async def register_with_agent(self):
        register_result = await self.agent_client.register(self.retry_count)
        if not register_result and not self.ignore_agent:
            logging.info('Registration failed. The program will exit now.')
            sys.exit(0)
    
    def schedule(self):
        asyncio.create_task(self.register_with_agent())

def main():
    config = Config()
    
    # Load logging configuration.
    with open(config.logging_config, 'r') as f:
        logging_config = yaml.safe_load(f)
        if config.debug:
            logging_config['root']['level'] = 'DEBUG'
            for k in logging_config['loggers']:
                logging_config['loggers'][k]['level'] = 'DEBUG'
        logging.config.dictConfig(logging_config)

    # Create a job that will register with the Agent after the Model API server started.
    public_endpoint = 'http://{0}:{1}{2}'.format(config.public_address, config.port, config.endpoint)
    agent_client = AgentClient(config.agent_endpoint, config.llm_name, public_endpoint, config.debug)
    logging.info('Public endpoint: {}'.format(public_endpoint))
    registration_job = RegistrationJob(agent_client, config.retry_count, config.ignore_agent)
    
    # Initialize the Model API server.
    model_layout = ModelLayout(config.layout_config, config.debug)
    server = ModelApiServer(
        config.endpoint, model_layout,
        on_startup=[registration_job.schedule],
        debug=config.debug
    )
    server.start(config.port, config.logging_config)