import socket, os, openai
from base import *

# -- Configs --
app.config["REDIS_URL"] = "redis://localhost:6379/0"
os.environ["CUDA_VISIBLE_DEVICES"] = "0"
app.agent_endpoint = "http://localhost:9000/"
app.LLM_name = "chatgpt"
app.version_code = "v1.0"
app.ignore_agent = False
# This is the IP that will be stored in Agent, Make sure the IP address here are accessible by Agent
public_ip = None
if public_ip == None: public_ip = socket.gethostbyname(socket.gethostname())
# The port to use, by choosing None, it'll assign an unused port
app.port = None 
if app.port == None:
    with socket.socket() as s:
        app.port = s.bind(('', 0)) or s.getsockname()[1]
path = "/"
app.reg_endpoint = f"http://{public_ip}:{app.port}{path}"
limit = 1024*3
model_loc = "gpt-3.5-turbo-0613"
api_key = None
usr_token = None
tc_model = None
# -- Config ends --

def llm_compute(data): 
    try:
        chatgpt_apitoken = data.get("chatgpt_apitoken")
        msg = [{"content":i['msg'], "role":"assistant" if i['isbot'] else "user"} for i in eval(data.get("input").replace("true","True").replace("false","False"))]
        
        if msg and chatgpt_apitoken:
            chatgpt_apitoken = chatgpt_apitoken.strip()
            if len(msg) > 0 and len(chatgpt_apitoken) > 0:
                openai.api_key = chatgpt_apitoken
                for i in openai.ChatCompletion.create(model="gpt-3.5-turbo",
                      max_tokens=limit,
                      temperature=0.5,
                      messages=msg).choices[0].message.content:
                    yield i
                    time.sleep(0.02)
                openai.api_key = None
            else:
                yield "No chatgpt token are received!" if len(msg) > 0 else "No input message are received!"
        else:
            yield "No chatgpt token are received!" if msg else "No input message are received!"
    except Exception as e:
        print(e)
        if str(e).startswith("Incorrect API key provided:"):
            yield "Incorrect API Key, You should provide a correct API key to use this LLM!"
        else:
            yield str(e)
    finally:
        app.Ready[0] = True
        print("finished")
app.llm_compute = llm_compute
start()