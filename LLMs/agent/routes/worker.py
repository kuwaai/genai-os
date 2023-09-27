from flask import Blueprint, request, Response
import json
from functools import reduce
from src.variable import *
worker = Blueprint('worker', __name__)

@worker.route("/schedule", methods=["POST"])
def status():
    # This will check if any LLM that is READY, then return "READY", if every is busy, return "BUSY"
    # Parameters: name, history_id, user_id
    llm_name, history_id, user_id = request.form.get("name"), request.form.get("history_id"), request.form.get("user_id")
    if llm_name and history_id and data.get(llm_name):
        for i in data[llm_name]:
            if i[1] == "READY" and i[2] == -1 and i[3] == -1:
                i[2] = history_id
                i[3] = user_id
                return "READY"
    return "BUSY"
   
@worker.route("/register", methods=["POST"])
def register():
    # For Online LLM register themself
    # Parameters: name, endpoint
    llm_name = request.json.get("name")
    endpoint = request.json.get("endpoint")
    if endpoint == None or llm_name == None:
        return Response(json.dumps({'message': 'Missing required field.'}), status=400)
    if reduce(lambda sum, d: sum or d[0]==endpoint, data.get(llm_name, []), False):
        return Response(json.dumps({'message': 'Duplicated worker endpoint.'}), status=400) 
    
    data.setdefault(llm_name, []).append([endpoint, "READY", -1, -1])
    return Response(status=201)

@worker.route("/unregister", methods=["POST"])
def unregister():
    # For Offline LLM to unregister themself
    # Parameters: name, endpoint
    llm_name = request.json.get("name")
    endpoint = request.json.get("endpoint")
    if llm_name in data:
        old = len(data[llm_name])
        data[llm_name] = [i for i in data[llm_name] if i[0] != endpoint]
        if data[llm_name] == []: del data[llm_name]
    
    # Failure means the Worker is not registered.
    # Thus, unregister is always successful.
    return Response(status=204)
    
@worker.route("/debug", methods=["GET"])
def debug():
    # This route is for debugging
    return data
    
@worker.route("/revoke", methods=["POST"])
def reset():
    # Reset specific status
    llm_name =request.json.get("name")
    history_id = request.form.get("history_id")
    if data.get(llm_name):
        dest = [i for i in data[llm_name] if i[2] == history_id]
        if len(dest) > 0:
            dest = dest[0]
            dest[3] = -1
            dest[2] = -1
            dest[1] = "READY"
    return Response(status=204)