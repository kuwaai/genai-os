import socket, os
import google.generativeai as genai
from base import *

# -- Configs --
app.config["REDIS_URL"] = "redis://127.0.0.1:6379/0"
os.environ["CUDA_VISIBLE_DEVICES"] = "0"
app.agent_endpoint = "http://127.0.0.1:9000/"
app.LLM_name = "gemini-pro"
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

genai.configure(api_key = "YOURAPIKEY")
model = genai.GenerativeModel('gemini-pro')
global proc
proc = False
def llm_compute(data): 
    global proc
    try:
        msg = [{"parts":[{"text":i['msg'].encode("utf-8",'ignore').decode("utf-8")}], "role":"model" if i['isbot'] else "user"} for i in eval(data.get("input").replace("true","True").replace("false","False"))]
        quiz = msg[-1]
        msg = msg[:-1]
        chat = model.start_chat(history=msg)
        proc = True
        for i in chat.send_message(quiz, stream=True,safety_settings={'HARASSMENT':'block_none','HARM_CATEGORY_DANGEROUS_CONTENT':'block_none','HARM_CATEGORY_HATE_SPEECH':'block_none',"HARM_CATEGORY_SEXUALLY_EXPLICIT":"block_none"}):
            for o in i.text:
                yield o
                print(end=o)
                time.sleep(0.01)
                if not proc: break
            if not proc: break
    except Exception as e:
        print(e)
        yield str(e)
    finally:
        proc = False
        app.Ready[0] = True
        print("finished")
        
def abort():
    global proc
    if proc:
        proc = False
        print("aborted")
        return "Aborted"
    return "No process to abort"
app.llm_compute = llm_compute
app.abort = abort
start()
