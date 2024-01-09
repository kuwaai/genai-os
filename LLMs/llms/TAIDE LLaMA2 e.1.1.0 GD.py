import socket, os, requests, json
from base import *

# -- Configs --
app.config["REDIS_URL"] = "redis://127.0.0.1:6379/0"
os.environ["CUDA_VISIBLE_DEVICES"] = "0"
app.agent_endpoint = "http://127.0.0.1:9000/"
app.LLM_name = "e.1.1.0-GD"
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
limit = 1024*14
model_loc = "safety-guard"
model_loc2 = "e.1.1.0"
usr_token = "92d1e9d60879348b8ed2f25f624012dcc596808dc40681d74c4965b8fff8a22a"
# -- Config ends --

def process_event(package):
    try:
        return json.loads(package)
    except json.JSONDecodeError as e:
        print(f"Error decoding JSON: {e}")

def llm_compute(data): 
    try:
        if data.get("input"):
            msg = eval(data.get("input").replace("true","True").replace("false","False"))
            url = "https://chatdev.gai.tw/v1.0/chat/completions"

            headers = {
                "Content-Type": "application/json",
                "Authorization": f"Bearer {usr_token}",
            }
            data1 = {
                "messages": msg,
                "model": model_loc
            }
            with requests.post(url, headers=headers, json=data1, stream=True,timeout=60) as response:
                for line in response.iter_lines(decode_unicode=True):
                    if line:
                        line = line.decode()
                        if line == "event: end":
                            break
                        elif line.startswith("data: "):
                            tmp = process_event(line[len("data: "):])["choices"][0]["delta"]["content"]
                            yield tmp[-1]
                            time.sleep(0.02)
            yield "\n\n-----\n"
            headers = {
                "Content-Type": "application/json",
                "Authorization": f"Bearer {usr_token}",
            }
            data1 = {
                "messages": msg,
                "model": model_loc2
            }
            result = ""
            with requests.post(url, headers=headers, json=data1, stream=True,timeout=60) as response:
                for line in response.iter_lines(decode_unicode=True):
                    if line:
                        line = line.decode()
                        if line == "event: end":
                            break
                        elif line.startswith("data: "):
                            tmp = process_event(line[len("data: "):])["choices"][0]["delta"]["content"]
                            yield tmp[-1]
                            result = tmp
                            time.sleep(0.02)
            headers = {
                "Content-Type": "application/json",
                "Authorization": f"Bearer {usr_token}",
            }
            data1 = {
                "messages": msg + [{"msg":result, "isbot":True}],
                "model": model_loc
            }
            with requests.post(url, headers=headers, json=data1, stream=True,timeout=60) as response:
                for line in response.iter_lines(decode_unicode=True):
                    if line:
                        line = line.decode()
                        if line == "event: end":
                            break
                        elif line.startswith("data: "):
                            tmp = process_event(line[len("data: "):])["choices"][0]["delta"]["content"]
                            if len(tmp) > len(result):
                                yield tmp[-1]
                                time.sleep(0.02)
    except Exception as e:
        print(e)
    finally:
        app.Ready[0] = True
        print("finished")
# model part ends
app.llm_compute = llm_compute
start()
