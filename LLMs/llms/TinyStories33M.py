import socket, os
from base import *

# -- Configs --
app.config["REDIS_URL"] = "redis://127.0.0.1:6379/0"
os.environ["CUDA_VISIBLE_DEVICES"] = "0"
app.agent_endpoint = "http://127.0.0.1:9000/"
app.LLM_name = "TinyStories-33M"
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
model_loc = "TinyStories-33M"
api_key = None
usr_token = None
tc_model = None
# -- Config ends --

from transformers import AutoModelForCausalLM, AutoTokenizer, GenerationConfig
    
model = AutoModelForCausalLM.from_pretrained(model_loc,device_map="cpu")
tokenizer = AutoTokenizer.from_pretrained("EleutherAI/gpt-neo-125M")
    
def llm_compute(data): 
    try:
        s = time.time()
        history = [i['msg'] for i in eval(data.get("input").replace("true","True").replace("false","False"))]
        if len(history) != 0:
            history = history[-1]
            encoding = tokenizer(history, return_tensors='pt', add_special_tokens=False).to(model.device)
            
            l = encoding['input_ids'].size(1)
            x = model.generate(
                **encoding,
                generation_config=GenerationConfig(
                    max_length=1000,
                    pad_token_id=tokenizer.eos_token_id,repetition_penalty=1.1
                )
            )
            result = tokenizer.batch_decode(x[:, l:])[0].strip().replace("<|endoftext|>","")
            print(result.encode("utf-8","ignore").decode("utf-8"))
            print(app.LLM_name, time.time() - s, len(result.strip()), len(result.strip())/(time.time() - s))
            for i in result:
                yield i
                time.sleep(0.01)

            torch.cuda.empty_cache()
        else:
            yield "Sorry, The input message is too huge!"

    except Exception as e:
        print(e)
    finally:
        torch.cuda.empty_cache()
        app.Ready[0] = True
        print("finished")
# model part ends
app.llm_compute = llm_compute
start()
