# -#- coding: UTF-8 -*-
# This demonstrated how to pipe the output of llm into another llm before returning the result.
import time, re, requests, sys, socket, os, torch
from flask import Flask, request, Response
from flask_sse import ServerSentEventsBlueprint
os.environ["CUDA_VISIBLE_DEVICES"] = "0"
app = Flask(__name__)
app.config["REDIS_URL"] = "redis://localhost:6379/0"
sse = ServerSentEventsBlueprint('sse', __name__)
app.register_blueprint(sse, url_prefix='/')
# -- Configs --
agent_endpoint = "http://localhost:9000/"
LLM_name = "dolly_v2_7b"
# This is the IP that will be stored in Agent,
# Make sure the IP address here are accessible by Agent
version_code = "v1.0"
ignore_agent = False
limit = 1024*3
model_loc = "databricks/dolly-v2-7b"
port = None # By choosing None, it'll assign an unused port
dummy = False
# -- Config ends --

if port == None:
    with socket.socket() as s:
        port = s.bind(('', 0)) or s.getsockname()[1]

Ready = [True]
if not dummy:
    # model part
    from transformers import pipeline
    pipe = pipeline(model=model_loc,task='text-generation',max_new_tokens=2048, top_p=0.92, top_k=0, do_sample=True, trust_remote_code=True, return_full_text=True)
    def process(data):
        try:
            history = [i['msg'] for i in eval(data.replace("true","True").replace("false","False"))]
            result = pipe(history[-1])[0]['generated_text']
            print(result)
            result = result.strip()
            
            for i in result:
                yield i
                time.sleep(0.02)

            torch.cuda.empty_cache()

        except Exception as e:
            print(e)
        finally:
            torch.cuda.empty_cache()
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
        data = request.form.get("input")
        resp = Response(process(data), mimetype='text/event-stream')
        resp.headers['Content-Type'] = 'text/event-stream; charset=utf-8'
        if data: return resp
        print("I didn't see your input!")
        Ready[0] = True
    return ""
registered = True
response = requests.post(agent_endpoint + f"{version_code}/worker/register", data={"name":LLM_name,"port":port})
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
            response = requests.post(agent_endpoint + f"{version_code}/worker/unregister", data={"name":LLM_name,"port":port})
            if response.text == "Failed":
                print("Warning, Failed to unregister from agent")
        except requests.exceptions.ConnectionError as e:
            print("Warning, Failed to unregister from agent")