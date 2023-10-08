#!/bin/python3
# -#- coding: UTF-8 -*-

import logging
import asyncio
import json
import prometheus_client
from functools import reduce
from dacite import from_dict, Config
from starlette.applications import Starlette
from starlette.routing import Route, Mount
from starlette.requests import Request
from sse_starlette.sse import EventSourceResponse
from starlette.responses import JSONResponse, Response, StreamingResponse

import model_api_server.metrics_helper as metrics_helper
from model_api_server.datatype import ChatRecord, Role
from model_api_server.model_layout import ModelLayout

health_check_endpoint = '/health'
prometheus_endpoint ='/metrics'

class InternalEndpointFilter(logging.Filter):
    """
    Filter out internal endpoint from access log.
    """


    def filter(self, record):
        # Aligned with uvicorn.logging.AccessFormatter
        (
            client_addr,
            method,
            full_path,
            http_version,
            status_code,
        ) = record.args
        
        # Note: Before [1] is solved, we need to exclude '/' for the mounted Prometheus exporter.
        # [1]: https://github.com/encode/starlette/issues/1336
        internal_endpoints = [health_check_endpoint, prometheus_endpoint, '/']
        result = reduce(
            lambda sum, x: sum and (full_path != x),
            internal_endpoints,
            True
        )
        return result

class ModelApiApplication:
    """
    ModelApiApplication encapsulates application logic of both the public endpoints and internal endpoints.
    """

    def __init__(self, endpoint: str, model_layout: ModelLayout, on_startup: list = [], debug = False):

        # Logger of this module
        self.logger = logging.getLogger(__name__) 
        if debug: self.logger.setLevel(logging.DEBUG)

        self.model_layout = model_layout
        
        # The web server to serve API endpoints 
        routes = [
            Route(endpoint, endpoint=self.api, methods=['POST']),
            Route(health_check_endpoint, endpoint=self.health_check, methods=['GET']),
            Mount(prometheus_endpoint, app=prometheus_client.make_asgi_app())
        ]
        self.app = Starlette(debug=debug, routes=routes, on_startup=on_startup)
        
        metric_prefix = 'api'
        self.metrics = metrics_helper.get_instance_with_prefix(metric_prefix, {
            'received_requests': {
                'type': prometheus_client.Counter,
                'description': 'Number of received requests.',
            },
            'accepted_requests': {
                'type': prometheus_client.Counter,
                'description': 'Number of accepted requests.',
            },
            'rejected_requests': {
                'type': prometheus_client.Counter,
                'description': 'Number of rejected requests.',
            },
        })

    def _get_chat_history(self, raw_chat_history: str) -> [ChatRecord]:
        raw_chat_history = json.loads(raw_chat_history)
        chat_history = [{**d, 'role': Role.BOT if d['isbot'] else Role.USER} for d in raw_chat_history]
        chat_history = [from_dict(data_class=ChatRecord, data=d) for d in chat_history]
        return chat_history
    
    async def api(self, request: Request):
        """
        The entrypoint of the public API.
        This function preprocesses the request and wraps the result from the LLM
        into a string stream.

        Arguments:
            request: The Request object from the Starlette framework
        """

        self.metrics['received_requests'].inc()

        if self.model_layout.is_busy():
            self.metrics['rejected_requests'].inc()
            return JSONResponse({'message': 'Processing another request'}, 503)
        
        chat_history = []
        async with request.form() as form:
            try:
                chat_history = self._get_chat_history(form.get('input'))
            except Exception as e:
                self.logger.exception('Error occurs when getting chat history.')
                self.metrics['rejected_requests'].inc()
                return JSONResponse({'message': "Bad request"}, 400)
        
        self.logger.debug('Chat history: {}'.format(chat_history))
            
        if chat_history == [] or chat_history[-1].msg == '':
            self.logger.debug("User input is empty.")
            self.metrics['rejected_requests'].inc()
            return JSONResponse({'message': "I didn't see your input!"}, 400)
        
        self.metrics['accepted_requests'].inc()
        response = StreamingResponse(self.model_layout.process(chat_history), media_type="text/plain")
        return response

    async def health_check(self, request: Request):
        return Response(status_code=204)