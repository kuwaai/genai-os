import logging
import json
from flask import Blueprint, request, json, redirect, url_for, jsonify
from src.variable import *
from src.functions import save_variable_to_file, endpoint_formatter, get_base_url, load_records
executor = Blueprint('executor', __name__)

logger = logging.getLogger(__name__)

@executor.route("/schedule", methods=["POST"])
def status():
    # This will check if any LLM that is READY, then return "READY", if every is busy, return "BUSY"
    # Parameters: name, history_id, user_id
    llm_name, history_id, user_id = request.form.get("name"), request.form.get("history_id"), request.form.get("user_id")
    if llm_name and history_id:
        if data.get(llm_name):
            for i in data[llm_name]:
                if i[1] == "READY" and i[2] == -1 and i[3] == -1:
                    i[2] = history_id
                    i[3] = user_id
                    logger.info(f"Scheduled {llm_name},{i[0]} for {history_id},{user_id}")
                    return "READY"
        else:
            logger.warning(f"No machine for {llm_name} has founded, returning NOMACHINE code")
            return "NOMACHINE"
    logger.warning(f"No READY machine for {llm_name}, returning BUSY code")
    return "BUSY"
   
@executor.route("/register", methods=["POST"])
def register():
    # For Online LLM register themself
    # Parameters: name, endpoint
    llm_name, endpoint = request.form.get("name"), request.form.get("endpoint")
    if endpoint == None or llm_name == None or endpoint_formatter(endpoint) in [j[0] for j in data.get(llm_name, [])]: return "Failed"
    data.setdefault(llm_name, []).append([endpoint_formatter(endpoint), "READY", -1, -1])
    save_variable_to_file(record_file, data)
    logger.info(f"A new {llm_name} is registered at {endpoint}")
    return "Success"

@executor.route("/unregister", methods=["POST"])
def unregister():
    # For Offline LLM to unregister themself
    # Parameters: name, endpoint
    llm_name, endpoint = request.form.get("name"), get_base_url(request.form.get("endpoint"))
    if llm_name in data:
        old = len(data[llm_name])
        data[llm_name] = [i for i in data[llm_name] if get_base_url(i[0]) != endpoint]
        if data[llm_name] == []: del data[llm_name]
        if data.get(llm_name) == None or old != len(data[llm_name]):
            save_variable_to_file(record_file, data)
            logger.info(f"{llm_name} , {endpoint} just unregistered from agent")
            return "Success"
    logger.warning(f"{llm_name} , {endpoint} failed to unregister")
    return "Failed"
    
@executor.route("/debug", methods=["GET", "POST"])
def debug():
    # This route is for debugging
    if request.method == 'POST':
        load_records(json.loads(request.form.get('data')), True)
        return redirect(url_for('executor.debug'))
    return """
<form method="POST">
    <textarea name="data" rows="4" cols="50">{0}</textarea><br>
    <input type="submit" value="Submit">
</form>
<script>
    document.querySelector("textarea").style.height = 'auto';
    document.querySelector("textarea").style.height = (document.querySelector("textarea").scrollHeight) + 'px';
</script>
""".format(str(json.dumps(data, indent=2)))

@executor.route("/list", methods=["GET"])
def list_executor():
    return jsonify(list(data.keys()))