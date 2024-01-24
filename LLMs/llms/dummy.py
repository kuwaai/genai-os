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
        for i in """你好我是個語言模型很高興認識你...之類的xD
<<<WARNING>>>
這是一個測試警告
<<</WARNING>>>
中途可以輸出警告
<<<WARNING>>>
警告2，嗨
<<</WARNING>>>
輸出文字模擬結束""":
            yield i
            time.sleep(0.1)
            if not proc: break
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