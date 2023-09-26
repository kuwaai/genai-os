import requests
from flask import Blueprint, request, Response
from src.variable import *
chat = Blueprint('chat', __name__)

@chat.route("/completions", methods=["POST"])
def completions():
    # Forward SSE stream to the READY state LLM API, If no exist then return empty message
    # Parameters: name, input, history_id, user_id
    llm_name, inputs, history_id, chatgpt_apitoken, user_id = request.form.get("name"), request.form.get("input"), request.form.get("history_id"), request.form.get("chatgpt_apitoken"), request.form.get("user_id")
    if data.get(llm_name):
        dest = [i for i in data[llm_name] if i[1] == "READY" and i[2] == history_id and i[3] == user_id]
        if len(dest) > 0:
            dest = dest[0]
            try:
                response = requests.post(dest[0], data={"input": inputs, "chatgpt_apitoken":chatgpt_apitoken}, stream=True)
                def event_stream(dest, response):
                    dest[1] = "BUSY"
                    try:
                        for c in response.iter_content(chunk_size=1):
                            yield c
                    except Exception as e:
                        print('Error: {0}'.format(str(e)))
                    finally:
                        dest[3] = -1
                        dest[2] = -1
                        dest[1] = "READY"
                        print("Done")
                return Response(event_stream(dest, response), mimetype='text/event-stream')
            except requests.exceptions.ConnectionError as e:
                #POST Failed, unregister this
                data[llm_name] = [i for i in data[llm_name] if i[0] != dest[0]]
                if data[llm_name] == []: del data[llm_name]
    return ""