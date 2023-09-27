#!/bin/python3
# -#- coding: UTF-8 -*-

import sys, os
import logging, yaml

from model_api_server.config import Config
from model_api_server.agent_client import AgentClient
from model_api_server.model_layout import ModelLayout
from model_api_server.api_server import ModelApiServer

def get_registration_job(agent_client, retry_count: int, ignore_agent: bool):
    """
    Establish the context of the registration job.
    """
    
    def register_with_agent():
        register_result = agent_client.register(retry_count)
        if not register_result and not ignore_agent:
            logging.info('Registration failed. The program will exit now.')
            sys.exit(0)

    return register_with_agent

def main():
    config = Config()
    
    # Load logging configuration.
    with open(config.logging_config, 'r') as f:
        logging.config.dictConfig(yaml.safe_load(f))
        
    # Create a job that will register with the Agent after the Model API server started.
    public_endpoint = 'http://{0}:{1}{2}'.format(config.public_address, config.port, config.endpoint)
    agent_client = AgentClient(config.agent_endpoint, config.llm_name, public_endpoint)
    logging.info('Public endpoint: {}'.format(public_endpoint))
    registration_job = get_registration_job(agent_client, config.retry_count, config.ignore_agent)
    
    # Initialize the Model API server.
    model_layout = ModelLayout(config.layout_config)
    server = ModelApiServer(config.endpoint, model_layout, on_startup=[registration_job])
    server.start(config.port, config.logging_config)