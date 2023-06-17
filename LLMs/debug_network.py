# -#- coding: UTF-8 -*-
import time, re, requests, sys, socket, os, torch
import numpy as np
from flask import Flask, request, Response
from flask_sse import ServerSentEventsBlueprint
os.environ["CUDA_VISIBLE_DEVICES"] = "1"
app = Flask(__name__)
app.config["REDIS_URL"] = "redis://localhost:6379/0"
sse = ServerSentEventsBlueprint('sse', __name__)
app.register_blueprint(sse, url_prefix='/')
# -- Configs --
agent_endpoint = "http://localhost:9000/"
LLM_name = "debug_network"
# This is the IP that will be stored in Agent, 
# Make sure the IP address here are accessible by Agent
public_ip = "localhost" 
ignore_agent = False
port = None # By choosing None, it'll assign an unused port
# -- Config ends --

if port == None:
    with socket.socket() as s:
        port = s.bind(('', 0)) or s.getsockname()[1]

Ready = [True]
def process(data): 
    try:
        yield data
    except Exception as e:
        print(e)
    finally:
        Ready[0] = True
        print("finished")

@app.route("/", methods=["POST"])
def api():
    if Ready[0]:
        Ready[0] = False
        data = request.form.get("input")
        resp = Response(process(data), mimetype='text/event-stream')
        resp.headers['Content-Type'] = 'text/event-stream; charset=utf-8'
        if data: return resp
        print("I didn't see your input!")
        Ready[0] = True
    return ""
registered = True
response = requests.post(agent_endpoint + "register", data={"name":LLM_name,"endpoint":"http://{0}:{1}/".format(public_ip, port)})
if response.text == "Failed":
    print("Warning, The server failed to register to agent")
    registered = False
    if not ignore_agent:
        print("The program will exit now.")
        sys.exit(0)
else:
    print("Registered")
    
if __name__ == '__main__':
    app.run(port=port, host="0.0.0.0")
    if registered:
        try:
            response = requests.post(agent_endpoint + "unregister", data={"name":LLM_name,"endpoint":"http://{0}:{1}/".format(public_ip, port)})
            if response.text == "Failed":
                print("Warning, Failed to unregister from agent")
        except requests.exceptions.ConnectionError as e:
            print("Warning, Failed to unregister from agent")