import socket, os
from base import *

# -- Configs --
app.config["REDIS_URL"] = "redis://localhost:6379/0"
os.environ["CUDA_VISIBLE_DEVICES"] = "0"
app.agent_endpoint = "http://localhost:9000/"
app.LLM_name = "debug_network"
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
# -- Config ends --
global proc
proc = None

def llm_compute(data): 
    global proc
    try:
        proc = True
        for i in "The crisp morning air tickled my face as I stepped outside. The sun was just starting to rise, casting a warm orange glow over the cityscape. I took a deep breath in, relishing in the freshness of the morning. As I walked down the street, the sounds of cars and chatter filled my ears. I could see people starting to emerge from their homes, ready to start their day.":
            yield i
            time.sleep(0.02)
            if proc: break
    except Exception as e:
        print(e)
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
# model part ends
app.llm_compute = llm_compute
app.abort = abort
start()