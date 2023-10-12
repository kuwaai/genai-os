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
    return "",404
    
@app.route('/health')
def health_check():
    return "", 204
    
@app.route("/health", methods=["GET"])
def api():
    if app.Ready[0]:
        app.Ready[0] = False
        data = request.form
        resp = Response(app.llm_compute(data), mimetype='text/event-stream')
        resp.headers['Content-Type'] = 'text/event-stream; charset=utf-8'
        if data: return resp
        print("I didn't see your input!")
        app.Ready[0] = True
    return ""

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