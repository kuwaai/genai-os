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
public_ip = "api"
if public_ip == None: public_ip = socket.gethostbyname(socket.gethostname())
# The port to use, by choosing None, it'll assign an unused port
app.port = 9001 
if app.port == None:
    with socket.socket() as s:
        app.port = s.bind(('', 0)) or s.getsockname()[1]
path = "/"
app.reg_endpoint = f"http://{public_ip}:{app.port}{path}"
limit = 1024*3
model_loc = "llama2-7b-chat-b1.0.0"
api_key = "uwU123DisApikEyiSASeCRetheHehee"
usr_token = "92d1e9d60879348b8ed2f25f624012dcc596808dc40681d74c4965b8fff8a22a"
tc_model = 26
# -- Config ends --

def llm_compute(data): 
    try:
        for i in "The crisp morning air tickled my face as I stepped outside. The sun was just starting to rise, casting a warm orange glow over the cityscape. I took a deep breath in, relishing in the freshness of the morning. As I walked down the street, the sounds of cars and chatter filled my ears. I could see people starting to emerge from their homes, ready to start their day.":
            yield i
            time.sleep(0.02)
    except Exception as e:
        print(e)
    finally:
        app.Ready[0] = True
        print("finished")
app.llm_compute = llm_compute
start()