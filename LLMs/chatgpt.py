# -#- coding: UTF-8 -*-
import time, requests, sys, socket, os, openai
from flask import Flask, request, Response
from flask_sse import ServerSentEventsBlueprint
os.environ["CUDA_VISIBLE_DEVICES"] = "1"
app = Flask(__name__)
app.config["REDIS_URL"] = "redis://localhost:6379/0"
sse = ServerSentEventsBlueprint('sse', __name__)
app.register_blueprint(sse, url_prefix='/')
# -- Configs --
agent_endpoint = "http://localhost:9000/"
LLM_name = "chatgpt"
model = "gpt-3.5-turbo-0613"
# This is the IP that will be stored in Agent, 
# Make sure the IP address here are accessible by Agent
public_ip = "localhost" 
ignore_agent = False
port = None # By choosing None, it'll assign an unused port
dummy = False
# -- Config ends --

if port == None:
    with socket.socket() as s:
        port = s.bind(('', 0)) or s.getsockname()[1]

Ready = [True]
if not dummy:
    # model part
    def process(data):
        try:
            if len(data) > 0 and data[0] > 0:
                if len(data) > 1 and 1 >= data[1] >= 0:
                    if len(data) > 2 and data[2] > 0:
                        yield openai.ChatCompletion.create(model=model,
                              max_tokens=data[2],
                              temperature=data[1],
                              messages=[
                              {"role": "user", "content": data[0]}
                            ]).choices[0].message.content
                    else:
                        yield "You must use at least 1 token to use this LLM!"
                else:
                    yield "Temperature out of range!"
            else:
                yield "No message received!"
        except Exception as e:
            print(e)
        finally:
            Ready[0] = True
            print("finished")
    # model part ends
else:
    def process(data): 
        try:
            for i in "The crisp morning air tickled my face as I stepped outside. The sun was just starting to rise, casting a warm orange glow over the cityscape. I took a deep breath in, relishing in the freshness of the morning. As I walked down the street, the sounds of cars and chatter filled my ears. I could see people starting to emerge from their homes, ready to start their day.":
                yield i
                time.sleep(0.02)
        except Exception as e:
            print(e)
        finally:
            Ready[0] = True
            print("finished")

@app.route("/", methods=["POST"])
def api():
    if Ready[0]:
        Ready[0] = False
        data = [request.form.get("input"), request.form.get("temperature"), request.form.get("max_tokens")]
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
    api_key = None
    with open("chatgpt_api_token", "r") as file:
        api_key = file.read()
    if api_key and api_key.strip():
        openai.api_key = api_key
        del api_key
        app.run(port=port, host="0.0.0.0")
        if registered:
            try:
                response = requests.post(agent_endpoint + "unregister", data={"name":LLM_name,"endpoint":"http://{0}:{1}/".format(public_ip, port)})
                if response.text == "Failed":
                    print("Warning, Failed to unregister from agent")
            except requests.exceptions.ConnectionError as e:
                print("Warning, Failed to unregister from agent")
    else:
        print("Setup failed, no API key founded!")