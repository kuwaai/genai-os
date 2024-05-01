import requests
from typing import List, Optional
from flask import Blueprint, request, Response
from ..variable import *
from ..safety_middleware import safety_middleware
chat = Blueprint('chat', __name__)

@chat.route("/completions", methods=["POST"])
def completions():
    # Forward SSE stream to the READY state LLM API, If no exist then return empty message
    # Parameters: name, input, history_id, user_id
    llm_name, inputs, history_id, openai_token, google_token, user_id, user_token, modelfile = request.form.get("name"), request.form.get("input"), request.form.get("history_id"), request.form.get("openai_token"), request.form.get("google_token"), request.form.get("user_id"), request.form.get("user_token"), request.form.get("modelfile")
    if data.get(llm_name):
        dest = [i for i in data[llm_name] if i[1] == "READY" and i[2] == history_id and i[3] == user_id]
        if len(dest) > 0:
            dest = dest[0]
            result = completions_backend(
                inputs=inputs,
                llm_name=llm_name,
                dest=dest,
                openai_token=openai_token,
                google_token=google_token,
                user_token=user_token,
                modelfile=modelfile,
            )
            return result
    return ""

@safety_middleware
def completions_backend(inputs:list, llm_name:str, dest:List, openai_token:Optional[str], google_token:Optional[str], user_token:Optional[str], modelfile:Optional[str]):
    """
    The backend portion of the completions endpoint. It forwards the user
    request to the backend.  This separation enables middleware can be installed
    through a decorator.
    Arguments:
        inputs: The prompt from the user.
        llm_name: The unique name of the model executors to be called.
        dest: The reference of the internal scheduling state. Note that the
        state should be reset after processing.
        openai_token: Specialized parameter for the ChatGPT model executor.
    Return:
        A generator object should be returned representing the streaming content.
        When encounter an error, we return an empty string here to be compatible
        with the original framework.
    """

    try:
        response = requests.post(dest[0], data={"input": inputs, "openai_token":openai_token, "google_token":google_token, "user_token":user_token, "modelfile":modelfile}, stream=True, timeout=120)
        def event_stream(dest, response):
            dest[1] = "BUSY"
            try:
                for c in response.iter_content(chunk_size=None, decode_unicode=True):
                    yield c
            except Exception as e:
                print('Error: {0}'.format(str(e)))
            finally:
                dest[3] = -1
                dest[2] = -1
                dest[1] = "READY"
                print("Done")
        return event_stream(dest, response), {'Content-Type': 'text/plain'}
    except requests.exceptions.ConnectionError as e:
        #POST Failed, unregister this LLM
        data[llm_name] = [i for i in data[llm_name] if i[0] != dest[0]]
        if data[llm_name] == []: del data[llm_name]
        return ""

@chat.route("/abort", methods=["POST"])
def abort():
    history_id, user_id = request.form.get("history_id"), request.form.get("user_id")
    if history_id and user_id:
        history_id = eval(history_id)
        for i, o in data.items():
            dest = [k for k in o if int(k[2]) in history_id and k[3] == user_id]
            for d in dest:
                requests.get(d[0] + "/abort", timeout=10)
    return "Success"