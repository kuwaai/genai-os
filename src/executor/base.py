import time
import re
import requests
import sys
import signal
import argparse
import os
import socket
import logging
import atexit
from urllib.parse import urljoin
from retry import retry
from flask import Flask, request, Response
from flask_sse import ServerSentEventsBlueprint

logger = logging.getLogger(__name__)
class LLMWorker:
    def __init__(self):
        self.app = Flask(__name__)
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
        parser.add_argument('--worker_path', default='/', help='The path this model worker is going to use')
        parser.add_argument('--limit', type=int, default=1024*3, help='Limit')
        parser.add_argument('--model_path', default=None, help='Model path')
        parser.add_argument('--prompt_path', default='', help='Prompt path')
        parser.add_argument('--gpu_config', default=None, help='GPU config')
        parser.add_argument('--agent_endpoint', default='http://127.0.0.1:9000/', help='Agent endpoint')
        parser.add_argument("--log", help="the log level. (INFO, DEBUG, ...)", type=str, default="INFO")
        return parser

    def _setup(self):
        # Setup logger
        numeric_level = getattr(logging, self.args.log.upper(), None)
        if not isinstance(numeric_level, int):
            raise ValueError(f'Invalid log level: {args.log}')
        logging.basicConfig(
            level=numeric_level,
            format='%(asctime)s [%(levelname)s]\t%(name)s - %(message)s',
            datefmt='%Y-%m-%d %H:%M:%S'
        )
        
        self.app.register_blueprint(ServerSentEventsBlueprint('sse', __name__), url_prefix=self.args.worker_path)
        self.Ready = True

        if self.args.gpu_config:
            os.environ["CUDA_VISIBLE_DEVICES"] = self.args.gpu_config

        self.agent_endpoint = self.args.agent_endpoint
        self.LLM_name = self.args.access_code
        self.model_path = self.args.model_path
        self.version_code = self.args.version
        self.ignore_agent = self.args.ignore_agent
        self.limit = self.args.limit
        self.public_ip = self.args.public_ip

        if self.public_ip == None:
            self.public_ip = socket.gethostbyname(socket.gethostname())

        self.port = self.args.port
        if self.port == None:
            with socket.socket() as s:
                self.port = s.bind(('', 0)) or s.getsockname()[1]

        self.reg_endpoint = f"http://{self.public_ip}:{self.port}{self.args.worker_path}"

        self._register_routes()

    def _register_routes(self):
        @self.app.route("/", methods=["POST"])
        def api():
            if self.Ready:
                self.Ready = False
                data = request.form
                resp = Response(self.llm_compute(data), mimetype='text/event-stream')
                resp.headers['Content-Type'] = 'text/event-stream; charset=utf-8'
                if data: return resp
                logger.info("Request received, but no data is here!")
                self.Ready = True
            return Response(status=429)
        
        @self.app.route('/health')
        def health_check():
            return Response(status=204)
        
        @self.app.route("/abort", methods=["GET"])
        def abort():
            if hasattr(self, 'abort') and callable(self.abort):
                return Response(self.abort(), mimetype='text/plain')
            return "No abort method configured"

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
        
        self.app.run(port=self.port, host="0.0.0.0", threaded=True)
        self._shut_down()

if __name__ == "__main__":
    worker = LLMWorker()
    worker.run()