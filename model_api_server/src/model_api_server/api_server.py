#!/bin/python3
# -#- coding: UTF-8 -*-

import logging
import asyncio
import uvicorn
from starlette.applications import Starlette
from starlette.routing import Route
from starlette.requests import Request
from sse_starlette.sse import EventSourceResponse
from starlette.responses import JSONResponse

from model_api_server.model_layout import ModelLayout

class ModelApiServer:
    """
    ModelApiServer is responsible to server the public endpoints.
    """

    def __init__(self, endpoint: str, model_layout: ModelLayout, on_startup: list = []):

        # Logger of this module
        self.logger = logging.getLogger(__name__) 

        self.model_layout = model_layout
        
        # The web server to serve API endpoints 
        routes = [
            Route(endpoint, endpoint=self.api, methods=['POST'])
        ]
        self.web_server = Starlette(debug=True, routes=routes, on_startup=on_startup)

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
            user_input = form.get('input')
            if user_input == None or user_input == '':
                self.logger.debug("I didn't see your input!")
                return JSONResponse({'message': "I didn't see your input!"}, 400)
            
            self.busy = True
            return EventSourceResponse(self.model_layout.process(user_input))