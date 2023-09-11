#!/bin/python3
# -#- coding: UTF-8 -*-

import sys, os, socket
import logging, yaml

import asyncio
import uvicorn
from starlette.applications import Starlette
from starlette.routing import Route
from sse_starlette.sse import EventSourceResponse
from starlette.responses import JSONResponse

from agent_client import AgentClient
from model_layout import ModelLayout

# from models.reflect import ReflectModel
# from filters.chinese_translate import OpenCC


class ModelApiServer:
    """
    ModelApiServer is responsible to server the public endpoints.
    """

    def __init__(self):
        # The default value of configuration.
        # The value can be override by specifying environment variables.
        self.config = {
            'agent_endpoint': 'http://localhost:9000/', # The root endpoint of the Agent.
            'LLM_name': 'Unnamed_LLM', # The name of this model.
            'public_ip': 'localhost', # The address that can be accessed by the Agent.
            'port': None, # The public port number for this API. Leave it as None to have it assigned by the system.
            'endpoint': '/v1/completion', # The endpoint of this Model API to serve external requests.
            'ignore_agent': False, # Continue running regardless of whether register successfully with the Agent.
            'retry_count': 5, # How may time should the API server try to register to the Agent
            'logging_config': './logging.yaml', # The path of the configuration file of logging module 
            'layout_config': './layouts/reflect.yaml', #The layout configuration to arrange the models and the filters
        }
        self.override_config()

        # Load logging configuration
        with open(self.config['logging_config'], 'r') as f:
            logging.config.dictConfig(yaml.safe_load(f))
        
        # Logger of this module
        self.logger = logging.getLogger(__name__) 
        
        # The Agent client to communicate with the Agent.
        public_endpoint = 'http://{0}:{1}{2}'.format(self.config['public_ip'], self.config['port'], self.config['endpoint'])
        self.agent_client = AgentClient(self.config['agent_endpoint'], self.config['LLM_name'], public_endpoint)
        
        # The layout to composite models and filters.
        self.model_layout = ModelLayout(self.config['layout_config'])

        # The web server to serve API endpoints 
        routes = [
            Route(self.config['endpoint'], endpoint=self.api, methods=['POST'])
        ]
        self.web_server = Starlette(debug=True, routes=routes, on_startup=[self.register_with_agent])
    
    def start(self):
        uvicorn.run(
            self.web_server,
            host='0.0.0.0',
            port=self.config['port'],
            log_config=self.config['logging_config']
        )
    
    
    async def api(self, request):
        """
        The entrypoint of the public API.
        This forward the result from the LLM and wrap them into an event source.

        Arguments:
            request: The Request object from the Starlette framework
        """

        if self.model_layout.is_busy(): return JSONResponse({'message': 'Processing another request'}, 503)
        
        async with request.form() as form:
            data = form.get('input')
            if data == None or data == '':
                self.logger.debug("I didn't see your input!")
                return JSONResponse({'message': "I didn't see your input!"}, 400)
            
            self.busy = True
            return EventSourceResponse(self.model_layout.process(data))

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

        self.config = {key: os.environ.get(key.upper(), default) for key, default in self.config.items()}
        self.config['port'] = self.config['port'] or assign_unused_port()
        self.config['ignore_agent'] = bool(self.config['ignore_agent'])
        self.config['retry_count'] = int(self.config['retry_count'])

    async def register_with_agent(self):
        """
        Register this Model API to the Agent.
        This task will be automatically invoked when the application is starting.
        """

        register_result = self.agent_client.register(self.config['retry_count'])

        if not register_result and not self.config['ignore_agent']:
            self.logger.info('Registration failed. The program will exit now.')
            sys.exit(0)


if __name__ == '__main__':
    
    os.environ['CUDA_VISIBLE_DEVICES'] = '1'

    server = ModelApiServer()
    server.start()