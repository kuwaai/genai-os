import socket, os
from base import *

# -- Configs --
app.config["REDIS_URL"] = "redis://localhost:6379/0"
os.environ["CUDA_VISIBLE_DEVICES"] = "0"
app.agent_endpoint = "http://localhost:9000/"
app.LLM_name = "dolly_v2_7b"
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
model_loc = "databricks/dolly-v2-7b"
api_key = None
usr_token = None
tc_model = None
# -- Config ends --

from transformers import pipeline
    pipe = pipeline(model=model_loc,task='text-generation',max_new_tokens=2048, top_p=0.92, top_k=0, do_sample=True, trust_remote_code=True, return_full_text=True)

def llm_compute(data): 
    try:
        history = [i['msg'] for i in eval(data.replace("true","True").replace("false","False"))]
        result = pipe(history[-1])[0]['generated_text']
        print(result)
        result = result.strip()
        
        for i in result:
            yield i
            time.sleep(0.02)

        torch.cuda.empty_cache()

    except Exception as e:
        print(e)
    finally:
        torch.cuda.empty_cache()
        Ready[0] = True
        print("finished")
app.llm_compute = llm_compute
start()