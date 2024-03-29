import socket, os
from base import *

# -- Configs --
app.config["REDIS_URL"] = "redis://redis:6379/0"
os.environ["CUDA_VISIBLE_DEVICES"] = "0"
app.agent_endpoint = "http://web:9000/"
app.LLM_name = "debug"
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
limit = None
model_loc = None
api_key = None
usr_token = None
tc_model = None
# -- Config ends --

def llm_compute(data): 
    try:
        yield [i['msg'] for i in eval(data.get("input").replace("true","True").replace("false","False"))][-1].strip()
    except Exception as e:
        print(e)
    finally:
        app.Ready[0] = True
        print("finished")
app.llm_compute = llm_compute
start()
