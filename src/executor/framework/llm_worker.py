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

import uvicorn
import prometheus_client
from retry import retry
from fastapi import FastAPI, Response, Request
from fastapi.responses import JSONResponse, StreamingResponse

from .metrics import WorkerMetrics
from .logger import WorkerLogger

logger = logging.getLogger(__name__)

class LLMWorker:
    def __init__(self):
        self.app = FastAPI()
        self.parser = self._create_parser()
        self.args = self.parser.parse_args()
        self._setup()

    def _create_parser(self):
        parser = argparse.ArgumentParser(description='LLM model worker, Please make sure your agent is working before use.')
        parser.add_argument('--access_code', default=None, help='Access code')
        parser.add_argument('--version', default='v1.0', help='Version')
        parser.add_argument('--ignore_agent', action='store_true', help='Ignore agent')
        parser.add_argument('--public_ip', default=None, help='This is the IP that will be stored in Agent, Make sure the IP address here are accessible by Agent')
        parser.add_argument('--port', type=int, default=None, help='The port to use, by choosing None, it\'ll assign an unused port')
        parser.add_argument('--worker_path', default='/chat', help='The path this model worker is going to use')
        parser.add_argument('--limit', type=int, default=1024*3, help='Limit')
        parser.add_argument('--model_path', default=None, help='Model path')
        parser.add_argument('--prompt_path', default='', help='Prompt path')
        parser.add_argument('--gpu_config', default=None, help='GPU config')
        parser.add_argument('--agent_endpoint', default='http://127.0.0.1:9000/', help='Agent endpoint')
        parser.add_argument("--log", type=str, default="INFO", help="The log level. (INFO, DEBUG, ...)")
        return parser

    def _setup(self):
        # Setup logger
        self.logging_config = WorkerLogger(level=self.args.log.upper())
        logging.config.dictConfig(self.logging_config.get_config())
        
        self.ready = True

        if self.args.gpu_config:
            os.environ["CUDA_VISIBLE_DEVICES"] = self.args.gpu_config

        self.agent_endpoint = self.args.agent_endpoint
        self.LLM_name = self.args.access_code
        self.model_path = self.args.model_path
        self.version_code = self.args.version
        self.ignore_agent = self.args.ignore_agent
        self.limit = self.args.limit
        self.public_ip = self.args.public_ip
        self.debug = (self.args.log.upper() == "DEBUG")

        if self.public_ip == None:
            self.public_ip = socket.gethostbyname(socket.gethostname())

        self.port = self.args.port
        if self.port == None:
            with socket.socket() as s:
                self.port = s.bind(('', 0)) or s.getsockname()[1]

        self.reg_endpoint = urljoin(f"http://{self.public_ip}:{self.port}/", self.args.worker_path)

        # Metrics
        self.metrics = WorkerMetrics(self.LLM_name)
        self.metrics.state.state('idle')

        self._register_routes()

    def _register_routes(self):
        @self.app.post(self.args.worker_path)
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
        
        @self.app.get(f"{self.args.worker_path}/abort")
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

    def _shut_down(self):
        if not hasattr(self, 'registered') or not self.registered:
            return
        try:
            response = requests.post(self.agent_endpoint + f"{self.version_code}/worker/unregister", data={"name":self.LLM_name,"endpoint":self.reg_endpoint})
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
            url=urljoin(self.agent_endpoint, f"{self.version_code}/worker/register"),
            data={"name": self.LLM_name, "endpoint": self.reg_endpoint}
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
        uvicorn.run(
            self.app, host='0.0.0.0', port=self.port,
            log_config=self.logging_config.get_config()
        )

    def _update_statistics(self, duration_sec: float, total_output_length: int):
        """
        Update the internal statistical metrics.
        """

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