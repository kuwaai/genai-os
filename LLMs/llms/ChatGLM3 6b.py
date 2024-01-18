import socket, os, emoji
from base import *

# -- Configs --
app.config["REDIS_URL"] = "redis://127.0.0.1:6379/0"
os.environ["CUDA_VISIBLE_DEVICES"] = "3,2"
app.agent_endpoint = "http://25.39.121.228:9000/"
app.LLM_name = "chatglm3-6b"
app.version_code = "v1.0"
app.ignore_agent = False
# This is the IP that will be stored in Agent, Make sure the IP address here are accessible by Agent
public_ip = "25.18.198.71"
if public_ip == None: public_ip = socket.gethostbyname(socket.gethostname())
# The port to use, by choosing None, it'll assign an unused port
app.port = None 
if app.port == None:
    with socket.socket() as s:
        app.port = s.bind(('', 0)) or s.getsockname()[1]
path = "/"
app.reg_endpoint = f"http://{public_ip}:{app.port}{path}"
limit = 1024*3
model_loc = "chatglm3-6b"
api_key = None
usr_token = None
tc_model = None
# -- Config ends --

from transformers import AutoModel, AutoTokenizer
    
model = AutoModel.from_pretrained(model_loc,device_map="auto", torch_dtype=torch.float16, trust_remote_code=True)
model.eval()
tokenizer = AutoTokenizer.from_pretrained(model_loc, trust_remote_code=True)
    
global proc
proc = False
def llm_compute(data): 
    global proc
    try:
        history = [{"role":("assistant" if i["isbot"] else "user"),"content":i['msg'].encode("utf-8","ignore").decode("utf-8")} for i in eval(data.get("input").replace("true","True").replace("false","False"))]
        while len("".join([i["content"] for i in history])) > limit:
            del history[0]
            del history[0]
        if len(history) != 0:
            print(history)
            length = 0
            proc = True
            for response, history in model.stream_chat(tokenizer, history[-1]["content"], history=history[:-1]):
                yield response[length:]
                length = len(response)
                if not proc: break

            torch.cuda.empty_cache()
        else:
            yield "Sorry, The input message is too huge!"

    except Exception as e:
        print(e)
    finally:
        torch.cuda.empty_cache()
        app.Ready[0] = True
        print("finished")
def abort():
    global proc
    if proc:
        proc = False
        torch.cuda.empty_cache()
        return "Aborted"
    return "No process to abort"
# model part ends
app.llm_compute = llm_compute
app.abort = abort
start()
