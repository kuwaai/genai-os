#!/bin/python3
# -#- coding: UTF-8 -*-

import logging
import asyncio
import uvicorn
import json
from functools import reduce
from dacite import from_dict, Config
from starlette.applications import Starlette
from starlette.routing import Route
from starlette.requests import Request
from sse_starlette.sse import EventSourceResponse
from starlette.responses import JSONResponse, Response, StreamingResponse

from model_api_server.datatype import ChatRecord, Role
from model_api_server.model_layout import ModelLayout

health_check_endpoint = '/health'

class HealthCheckFilter(logging.Filter):
    def filter(self, record):
        return record.getMessage().find(health_check_endpoint) == -1
class ModelApiServer:
    """
    ModelApiServer is responsible to server the public endpoints.
    """

    def __init__(self, endpoint: str, model_layout: ModelLayout, on_startup: list = [], debug = False):

        # Logger of this module
        self.logger = logging.getLogger(__name__) 
        if debug: self.logger.setLevel(logging.DEBUG)

        self.model_layout = model_layout
        
        # The web server to serve API endpoints 
        routes = [
            Route(endpoint, endpoint=self.api, methods=['POST']),
            Route(health_check_endpoint, endpoint=self.health_check, methods=['GET'])
        ]
        self.web_server = Starlette(debug=debug, routes=routes, on_startup=on_startup)

    def start(self, port: int, logging_config: str):
        """
        Start the web server.

        Arguments:
            port: Port number to serve web requests.
            logging_config: The path of logging configuration file.
        """

        uvicorn.run(
            self.web_server,
            host='0.0.0.0',
            port=port,
            log_config=logging_config
        )
    
    async def api(self, request: Request):
        """
        The entrypoint of the public API.
        This forward the result from the LLM and wrap them into an event source.

        Arguments:
            request: The Request object from the Starlette framework
        """

        if self.model_layout.is_busy(): return JSONResponse({'message': 'Processing another request'}, 503)
        
        async with request.form() as form:
            user_input = []
            try:
                user_input = json.loads(form.get('input'))
                user_input = [{**d, 'role': Role.BOT if d['isbot'] else Role.USER} for d in user_input]
                user_input = [from_dict(data_class=ChatRecord, data=d) for d in user_input]
            except Exception as e:
                self.logger.error(e)
                return JSONResponse({'message': "Bad request"}, 400)
            self.logger.debug('Input: {}'.format(user_input))
            
            if user_input == [] or user_input[-1].msg == '':
                self.logger.debug("I didn't see your input!")
                return JSONResponse({'message': "I didn't see your input!"}, 400)
            
            self.busy = True

            response = StreamingResponse(self.model_layout.process(user_input), media_type="text/plain")
            return response

    async def health_check(self, request: Request):
        return Response(status_code=204)