#!/bin/python3
# -#- coding: UTF-8 -*-

import os, socket

class Config:
    """
    The configuration of model API component.
    The value can be override by specifying environment variables.
    """

    def __init__(self):
        
        # Default configuration
        self.agent_endpoint = 'http://localhost:9000/' # The root endpoint of the Agent.
        self.llm_name: str = 'Unnamed_LLM' # The name of this model.
        self.public_address: str = 'localhost' # The address that can be accessed by the Agent.
        self.port: int = None # The public port number for this API. Leave it as None to have it assigned by the system.
        self.endpoint: str = '/v1/completion' # The endpoint of this Model API to serve external requests.
        self.ignore_agent: bool = False # Continue running regardless of whether register successfully with the Agent.
        self.retry_count: int = 5 # How may time should the API server try to register to the Agent
        self.logging_config: str = './logging.yaml' # The path of the configuration file of logging module 
        self.layout_config: str = './layouts/reflect.yaml' #The layout configuration to arrange the models and the filters

        self.override_config()

    def override_config(self):
        """
        Override default configuration if the corresponding environment variable exists.
        """
        
        def assign_unused_port():
            """
            Probe the unused port.
            The OS should assigned a unused port to this application.
            """

            sock = socket.socket()
            sock.bind(('', 0))
            port = sock.getsockname()[1]
            sock.close()
            return port

        for key, default in vars(self).items():
            setattr(self, key, os.environ.get(key.upper(), default))

        self.port = int(self.port or assign_unused_port())
        self.ignore_agent = bool(self.ignore_agent)
        self.retry_count = int(self.retry_count)
