<<<<<<< HEAD
# -#- coding: UTF-8 -*-
# This demonstrated how to pipe the output of llm into another llm before returning the result.
import time, re, requests, sys, torch, signal
from flask import Flask, request, Response
from flask_sse import ServerSentEventsBlueprint
app = Flask(__name__)
app.register_blueprint(ServerSentEventsBlueprint('sse', __name__), url_prefix='/')
app.Ready = [True]

@app.route("/", methods=["POST"])
def api():
    if app.Ready[0]:
        app.Ready[0] = False
        data = request.form
        resp = Response(app.llm_compute(data), mimetype='text/event-stream')
        resp.headers['Content-Type'] = 'text/event-stream; charset=utf-8'
        if data: return resp
        print("Request received, but no data is here!")
        app.Ready[0] = True
    return Response(status=404)
    
@app.route('/health')
def health_check():
    return Response(status=204)
    
@app.route("/abort", methods=["GET"])
def abort():
    if app.abort:
        return Response(app.abort(), mimetype='text/plain')
    return "No abort method configured"

def shut():
    if app.registered:
        try:
            response = requests.post(app.agent_endpoint + f"{app.version_code}/worker/unregister", data={"name":app.LLM_name,"endpoint":app.reg_endpoint})
            if response.text == "Failed":
                print("Warning, Failed to unregister from agent")
        except requests.exceptions.ConnectionError as e:
            print("Warning, Failed to unregister from agent")

def handler(signum, frame):
    print("Received SIGTERM, exiting...")
    shut()
    sys.exit(0)
signal.signal(signal.SIGTERM, handler)

def start():
    app.registered = True
    response = requests.post(app.agent_endpoint + f"{app.version_code}/worker/register", data={"name":app.LLM_name,"endpoint":app.reg_endpoint})
    if response.text == "Failed":
        print("Warning, The server failed to register to agent")
        app.registered = False
        if not app.ignore_agent:
            print("The program will exit now.")
            sys.exit(0)
    else:
        print("Registered")
    app.run(port=app.port, host="0.0.0.0", threaded=True)
    shut()
=======
import time
import re
import requests
import sys
import torch
import signal
import argparse
import os
import socket
from flask import Flask, request, Response
from flask_sse import ServerSentEventsBlueprint

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
        return parser

    def _setup(self):
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
                print("Request received, but no data is here!")
                self.Ready = True
            return Response(status=404)
        
        @self.app.route('/health')
        def health_check():
            return Response(status=204)
        
        @self.app.route("/abort", methods=["GET"])
        def abort():
            if self.abort:
                return Response(self.abort(), mimetype='text/plain')
            return "No abort method configured"

    def run(self):
        self._register_signals()
        self._start_server()

    def _register_signals(self):
        signal.signal(signal.SIGTERM, self._handle_sigterm)

    def _handle_sigterm(self, signum, frame):
        print("Received SIGTERM, exiting...")
        self._shut_down()
        sys.exit(0)

    def _shut_down(self):
        if hasattr(self, 'registered') and self.registered:
            try:
                response = requests.post(self.agent_endpoint + f"{self.version_code}/worker/unregister", data={"name":self.LLM_name,"endpoint":self.reg_endpoint})
                if response.text == "Failed":
                    print("Warning, Failed to unregister from agent")
            except requests.exceptions.ConnectionError as e:
                print("Warning, Failed to unregister from agent")

    def _start_server(self):
        self.registered = True
        response = requests.post(self.agent_endpoint + f"{self.version_code}/worker/register", data={"name":self.LLM_name,"endpoint":self.reg_endpoint})
        if response.text == "Failed":
            print("Warning, The server failed to register to agent")
            self.registered = False
            if not self.ignore_agent:
                print("The program will exit now.")
                sys.exit(0)
        else:
            print("Registered")
        self.app.run(port=self.port, host="0.0.0.0", threaded=True)
        self._shut_down()

if __name__ == "__main__":
    worker = LLMWorker()
    worker.run()
>>>>>>> 0cbbb60a4f1bce269c45504f8d6008ef1cb1e4d1
