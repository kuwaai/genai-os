import socket, os
from base import *

# -- Configs --
app.config["REDIS_URL"] = "redis://192.168.211.4:6379/0"
os.environ["CUDA_VISIBLE_DEVICES"] = ""
app.agent_endpoint = "http://192.168.211.4:9000/"
app.LLM_name = "llama_cpp_b.11.0.0_q4_0"
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
api_key = None
usr_token = None
tc_model = None
# -- Config ends --

from llama_cpp import Llama
    
model = Llama(model_path="./ggml-model-q4_0.gguf")
prompts = "<s>[INST] {0} [/INST]{1}"
    
def llm_compute(data): 
    try:
        s = time.time()
        history = [i['msg'] for i in eval(data.get("input").replace("true","True").replace("false","False"))]
        while len("".join(history)) > limit:
            del history[0]
            del history[0]
        if len(history) != 0:
            #history[0] = "<<SYS>>\n\n<</SYS>>\n\n" + history[0]
            history.append("")
            history = [prompts.format(history[i], ("{0}" if i+1 == len(history) - 1 else " {0} </s>").format(history[i + 1])) for i in range(0, len(history), 2)]
            history = "".join(history)
            output = model.create_completion(
                  history,
                  max_tokens=4096,
                  stop=["</s>"],
                  echo=False,
                  stream=True
            )
            
            for i in output:
                print(end=i["choices"][0]["text"],flush=True)
                yield i["choices"][0]["text"]
        else:
            yield "Sorry, The input message is too huge!"

    except Exception as e:
        print(e)
    finally:
        app.Ready[0] = True
        print("finished")
# model part ends
app.llm_compute = llm_compute
start()
