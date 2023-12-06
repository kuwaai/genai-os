import socket, os
from base import *

# -- Configs --
app.config["REDIS_URL"] = "redis://192.168.211.4:6379/0"
os.environ["CUDA_VISIBLE_DEVICES"] = "0"
app.agent_endpoint = "http://192.168.211.4:9000/"
app.LLM_name = "llama2_b.11.0.0-4bits"
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
model_loc = "llama2-7b-b.11.0.0-4bits"
api_key = None
usr_token = None
tc_model = None
# -- Config ends --

from transformers import AutoTokenizer, GenerationConfig, TextIteratorStreamer, set_seed
from intel_extension_for_transformers.transformers import AutoModelForCausalLM
from threading import Thread
    
set_seed(42)
model = AutoModelForCausalLM.from_pretrained(model_loc, torch_dtype=torch.float16,device_map="auto")
tokenizer = AutoTokenizer.from_pretrained(model_loc)
prompts = "<s>[INST] {0} [/INST]{1}"
    
def llm_compute(data): 
    try:
        s = time.time()
        history = [i['msg'] for i in eval(data.get("input").replace("true","True").replace("false","False"))]
        #while len("".join(history)) > limit:
        #    del history[0]
        #    del history[0]
        if len(history) != 0:
            #history[0] = "<<SYS>>\n\n<</SYS>>\n\n" + history[0]
            history.append("")
            history = [prompts.format(history[i], ("{0}" if i+1 == len(history) - 1 else " {0} </s>").format(history[i + 1])) for i in range(0, len(history), 2)]
            history = "".join(history)
            encoding = tokenizer(history, return_tensors='pt', add_special_tokens=False).to(model.device)
            
            streamer = TextIteratorStreamer(tokenizer,skip_prompt=True)
            l = encoding['input_ids'].size(1)
            thread = Thread(target=model.generate, kwargs=dict(input_ids=encoding['input_ids'], streamer=streamer,
                generation_config=GenerationConfig(
                    max_new_tokens=4096,
                    pad_token_id=tokenizer.eos_token_id,
                    temperature = 0.2,
                    repetition_penalty = 1.0,
                    do_sample=True
                )))
            thread.start()
            for i in streamer:
                print(end=i.replace("</s>",""),flush=True)
                yield i.replace("</s>","")

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
