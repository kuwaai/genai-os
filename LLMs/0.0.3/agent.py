# -#- coding: UTF-8 -*-
import time, re, requests
from flask import Flask, request, Response
from flask_sse import ServerSentEventsBlueprint
app = Flask(__name__)
app.config["REDIS_URL"] = "redis://localhost:6379/0"
sse = ServerSentEventsBlueprint('sse', __name__)
app.register_blueprint(sse, url_prefix='/')
data = {}

@app.route("/", methods=["POST"])
def api():
    # Forward SSE stream to the READY state LLM API, If no exist then return empty message
    # Parameters: name, input, history_id
    llm_name, inputs, history_id = request.form.get("name"), request.form.get("input"), request.form.get("history_id")
    if data.get(llm_name):
        dest = [i for i in data[llm_name] if i[1] == "READY" and i[2] == history_id]
        if len(dest) > 0:
            dest = dest[0]
            try:
                response = requests.post(dest[0], data={"input": inputs, "name":llm_name, "history_id":history_id}, stream=True)
                def event_stream(dest, response):
                    dest[1] = "BUSY"
                    try:
                        for c in response.iter_content(chunk_size=1):
                            yield c
                    except Exception as e:
                        print('Error: {0}'.format(str(e)))
                    dest[2] = -1
                    dest[1] = "READY"
                    print("Finished")
                return Response(event_stream(dest, response), mimetype='text/event-stream')
            except requests.exceptions.ConnectionError as e:
                #POST Failed, unregister this
                data[llm_name] = [i for i in data[llm_name] if i[0] != endpoint]
                if data[llm_name] == []: del data[llm_name]
    return ""
    
@app.route("/status", methods=["POST"])
def status():
    # This will check if any LLM that is READY, then return "READY", if every is busy, return "BUSY"
    # Parameters: name, history_id
    llm_name, history_id = request.form.get("name"), request.form.get("history_id")
    if data.get(llm_name):
        for i in data[llm_name]:
            if i[1] == "READY" and i[2] == -1:
                i[2] = history_id
                return "READY"
    return "BUSY"
   
@app.route("/register", methods=["POST"])
def register():
    # For Online LLM register themself
    # Parameters: name, endpoint
    llm_name, endpoint = request.form.get("name"), request.form.get("endpoint")
    if endpoint == None or llm_name == None or endpoint in data.get(llm_name, []): return "Failed"
    data.setdefault(llm_name, []).append([endpoint, "READY", -1])
    return "Success"

@app.route("/unregister", methods=["POST"])
def unregister():
    # For Offline LLM to unregister themself
    # Parameters: name, endpoint
    llm_name, endpoint = request.form.get("name"), request.form.get("endpoint")
    if llm_name in data:
        old = len(data[llm_name])
        data[llm_name] = [i for i in data[llm_name] if i[0] != endpoint]
        if data[llm_name] == []: del data[llm_name]
        if data.get(llm_name) == None or old == len(data[llm_name]):
            return "Success"
    return "Failed"
    
@app.route("/debug", methods=["GET"])
def debug():
    # This route is for debugging
    return data
    
if __name__ == '__main__':
    app.run(port=9000, host="0.0.0.0", debug=True)
