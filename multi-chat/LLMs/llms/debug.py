import socket, os, time
from base import *

# -- Configs --
os.environ["CUDA_VISIBLE_DEVICES"] = "0"
app.agent_endpoint = "http://127.0.0.1:9000/"
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

def llm_compute(data): 
    try:
        for i in "".join([i['msg'] for i in eval(data.get("input").replace("true","True").replace("false","False"))]).strip():
            yield i
            time.sleep(0.02)
    except Exception as e:
        print(e)
    finally:
        app.Ready[0] = True
        print("finished")
app.llm_compute = llm_compute
start()