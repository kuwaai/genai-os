import requests
import sys
import argparse
import os
import socket
import time
import logging
import atexit
import asyncio
from urllib.parse import urljoin
from typing import Optional

import uvicorn
import prometheus_client
from retry import retry
from fastapi import FastAPI, Response, Request
from fastapi.responses import JSONResponse, StreamingResponse

from .metrics import WorkerMetrics
from .logger import WorkerLoggerFactory

logger = logging.getLogger(__name__)

def find_free_port():
    port = None
    with socket.socket() as s:
        port = s.bind(('', 0)) or s.getsockname()[1]
    return port

class LLMWorker:
    executor_iface_version: str = "v1.0"
    agent_url: str = "http://127.0.0.1:9000/"
    ignore_agent: bool = False
    https: bool = False
    host: Optional[str] = None
    port: Optional[int] = None
    worker_path: str = "/chat"
    LLM_name: Optional[str] = None

    ready: bool = False

    log_level: str = "INFO"
    metrics: Optional[WorkerMetrics] = None

    def __init__(self):
        self.app = FastAPI()
        self.parser = self._create_parser()
        self.args = self.parser.parse_args()
        self._setup()

    def _create_parser(self):
        parser = argparse.ArgumentParser(description='LLM model worker, Please make sure your agent is working before use.')
        parser.add_argument('--access_code', default=self.LLM_name, help='Access code')
        parser.add_argument('--version', default=self.executor_iface_version, help='Version of the executor interface')
        parser.add_argument('--ignore_agent', action='store_true', help='Ignore agent')
        parser.add_argument('--https', action='store_true', help='Register the worker endpoint with https scheme')
        parser.add_argument('--host', default=None, help='The hostname or IP address that will be stored in Agent, Make sure the location are accessible by Agent')
        parser.add_argument('--port', type=int, default=None, help='The port to serve. By choosing None, it\'ll assign an unused port')
        parser.add_argument('--worker_path', default=self.worker_path, help='The path this model worker is going to use')
        parser.add_argument('--agent_url', default=self.agent_url, help='Base URL of Agent\'s executor management API')
        parser.add_argument("--log", type=str.upper, default=self.log_level, help="The logging level.", choices=["NOTSET", "DEBUG", "INFO", "WARNING", "ERROR", "CRITICAL"])
        return parser

    def _setup(self):
        # Setup logger
        self.log_level = self.args.log.upper()
        logging.config.dictConfig(WorkerLoggerFactory(level=self.log_level).get_config())
        
        # Registration information
        self.executor_iface_version = self.args.version
        self.agent_url = self.args.agent_url
        self.ignore_agent = self.args.ignore_agent
        self.LLM_name = self.args.access_code

        # Serving URL
        self.host = self.args.host or socket.gethostbyname(socket.gethostname())
        self.port = self.args.port or find_free_port()
        self.https = self.args.https
        self.worker_path = self.args.worker_path

        # Metrics
        self.metrics = WorkerMetrics(self.LLM_name)
        self.metrics.state.state('idle')

        self._register_routes()

    def _register_routes(self):
        @self.app.post(self.worker_path)
        async def api(request: Request):
            if not self.ready:
                return JSONResponse({"msg": "Processing another request."}, status_code=429)
            data = await request.form()
            if not data:
                logger.debug("Received empty request!")
                return JSONResponse({"msg": "Received empty request!"}, status_code=400)
            resp = StreamingResponse(
                self._llm_compute(data),
                media_type='text/event-stream',
                headers = {'Content-Type': 'text/event-stream; charset=utf-8'}
            )
            return resp
        
        @self.app.get("/health")
        async def health_check():
            return Response(status_code=204)
        
        @self.app.get(urljoin(f"{self.worker_path}/", "./abort"))
        async def abort():
            if hasattr(self, 'abort') and callable(self.abort):
                return JSONResponse({"msg": await self.abort()})
            return JSONResponse({"msg": "No abort method configured"}, status_code=404)

        @self.app.get("/metrics")
        async def get_metrics():
            return Response(
                content=prometheus_client.generate_latest(),
                media_type="text/plain"
            )

    def run(self):
        atexit.register(self._shut_down)
        self._start_server()
    
    def get_reg_endpoint(self) -> str:
        scheme = 'https' if self.args.https else 'http'
        return urljoin(f"{scheme}://{self.host}:{self.port}/", self.worker_path)

    def in_debug(self) -> bool:
        return (self.log_level.upper() == "DEBUG")

    def _shut_down(self):
        if not hasattr(self, 'registered') or not self.registered:
            return
        try:
            response = requests.post(
                urljoin(self.agent_url, f"{self.executor_iface_version}/worker/unregister"),
                data={"name": self.LLM_name,"endpoint": self.get_reg_endpoint()}
            )
            if not response.ok or response.text == "Failed":
                raise RuntimeWarning()
            else:
                logger.info("Unregistered from agent.")
                self.registered = False
        except requests.exceptions.ConnectionError as e:
            logger.warning("Failed to unregister from agent")

    @retry(tries=5, delay=1, backoff=2, jitter=(0, 1), logger=logger)
    def _try_register(self):
        resp = requests.post(
            url=urljoin(self.agent_url, f"{self.executor_iface_version}/worker/register"),
            data={"name": self.LLM_name, "endpoint": self.get_reg_endpoint()}
        )
        if not resp.ok or resp.text == "Failed":
            raise RuntimeWarning("The server failed to register to agent.")
    
    def _start_server(self):
        self.registered = False
        if not self.ignore_agent:
            try:
                self._try_register()
                logger.info(f"Registered with the name \"{self.LLM_name}\"")
                self.registered = True

            except Exception as e:
                logger.exception("Failed to register to agent.")

                if not self.ignore_agent:
                    logger.info("The program will exit now.")
                    sys.exit(0)
        self.ready = True
        uvicorn.run(
            self.app, host=self.host, port=self.port,
            log_config=WorkerLoggerFactory(level=self.log_level).get_config()
        )

    def _update_statistics(self, duration_sec: float, total_output_length: int):
        """
        Update the internal statistical metrics.
        """
        if duration_sec > 0:
            throughput = total_output_length/duration_sec
            self.metrics.process_time_seconds.observe(duration_sec)
            self.metrics.output_length_charters.observe(total_output_length)
            self.metrics.output_throughput_charters_per_second.observe(throughput)
    
    async def _llm_compute(self, data):
        """
        The middle layer between the actual worker logic and API server logic.
        Interception of the request-response can be done in this layer.
        """

        self.ready = False
        self.metrics.state.state('busy')
        try:
            start_time = time.time()
            total_output_length = 0
            
            async for chunk in self.llm_compute(data):
                total_output_length += len(chunk)
                yield chunk

                # Yield control to the event loop.
                # So that other coroutine, like aborting, can run.
                await asyncio.sleep(0)

            duration_sec = time.time() - start_time
            self._update_statistics(duration_sec, total_output_length)

        except Exception as e:
            logger.exception("Error occurs during generation.")
            self.metrics.failed.inc()
            yield 'Error occurred. Please consult support.'

        finally:
            self.metrics.state.state('idle')
            self.ready = True

    async def llm_compute(self, data):
        raise NotImplementedError("Worker should implement the \"llm_compute\" method.")

if __name__ == "__main__":
    worker = LLMWorker()
    worker.run()