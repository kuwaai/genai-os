import requests, json
from flask import Blueprint, request, Response
from src.variable import *
from src.functions import *
chat = Blueprint('chat', __name__)

@chat.route("/completions", methods=["POST"])
def completions():
    # Forward SSE stream to the READY state LLM API, If no exist then return empty message
    # Parameters: name, input, history_id, user_id
    llm_name, inputs, history_id, chatgpt_apitoken, user_id = request.form.get("name"), request.form.get("input"), request.form.get("history_id"), request.form.get("chatgpt_apitoken"), request.form.get("user_id")
    if data.get(llm_name):
        dest = [i for i in data[llm_name] if i[5] or (i[1] == "READY" and i[2] == history_id and i[3] == user_id)]
        if len(dest) > 0:
            dest = dest[0]
            try:
                response = requests.post(dest[0], data={"input": inputs, "chatgpt_apitoken":chatgpt_apitoken}, stream=True)
                def event_stream(dest, response):
                    if not dest[5]:
                        dest[1] = "BUSY"
                    try:
                        if dest[4] and safety_guard: buffer = b""
                        old_len = 0
                        for c in response.iter_content(chunk_size=1):
                            if dest[4] and safety_guard:
                                buffer += c
                                tmp = len(buffer.decode("utf-8","ignore"))
                                if old_len != tmp and tmp % 50 == 0:
                                    old_len = tmp
                                    print(old_len, tmp)
                                    #Safety guard check
                                    res = requests.post(f"http://127.0.0.1:{port}/v1.0/worker/schedule", data={
                                        "name": safety_guard,
                                        "history_id": history_id,
                                        "user_id": user_id
                                    })
                                    if res.status_code == 200:
                                        print(res.text)
                                        if res.text == "READY":
                                            res = requests.post(f"http://127.0.0.1:{port}/v1.0/chat/completions", data={
                                                "name": safety_guard,
                                                "history_id": history_id,
                                                "user_id": user_id,
                                                "input":json.dumps(eval(inputs.replace("true","True").replace("false","False")) + [
                                                    { "msg": buffer.decode("utf-8","ignore"), "isbot": True }
                                                ])
                                            })
                                            if res.status_code == 200:
                                                print(res.text)
                                                if not res.text.endswith("\n沒有違反規則。"):
                                                    requests.get(dest[0] + "/abort")
                                                    print("Unsafe! Send abort!")
                                                    break
                                                print("Safe")
                            yield c
                    except Exception as e:
                        print('Error: {0}'.format(str(e)))
                    finally:
                        if not dest[5]:
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

@chat.route("/abort", methods=["POST"])
def abort():
    history_id, user_id = request.form.get("history_id"), request.form.get("user_id")
    if history_id and user_id:
        history_id = eval(history_id)
        for i, o in data.items():
            dest = [k for k in o if int(k[2]) in history_id and k[3] == user_id]
            for d in dest:
                requests.get(d[0] + "/abort")
    return "Success"