import socket, os
from base import *

# -- Configs --
app.config["REDIS_URL"] = "redis://127.0.0.1:6379/0"
os.environ["CUDA_VISIBLE_DEVICES"] = "5,4"
app.agent_endpoint = "http://25.39.121.228:9000/"
app.LLM_name = "meta_llama2_7b"
app.version_code = "v1.0"
app.ignore_agent = False
# This is the IP that will be stored in Agent, Make sure the IP address here are accessible by Agent
public_ip = "25.18.198.71"
if public_ip == None: public_ip = socket.gethostbyname(socket.gethostname())
# The port to use, by choosing None, it'll assign an unused port
app.port = None 
if app.port == None:
    with socket.socket() as s:
        app.port = s.bind(('', 0)) or s.getsockname()[1]
path = "/"
app.reg_endpoint = f"http://{public_ip}:{app.port}{path}"
limit = 1024*14
model_loc = "Llama-2-7b-chat-hf"
api_key = None
usr_token = None
tc_model = None
# -- Config ends --

from transformers import AutoTokenizer, GenerationConfig, TextIteratorStreamer, StoppingCriteria, StoppingCriteriaList
from intel_extension_for_transformers.transformers import AutoModelForCausalLM
from threading import Thread
class CustomStoppingCriteria(StoppingCriteria):
    def __init__(self):
        pass

    def __call__(self, input_ids, score, **kwargs) -> bool:
        global proc
        return not proc
model = AutoModelForCausalLM.from_pretrained(model_loc,device_map="auto", torch_dtype=torch.float16)
tokenizer = AutoTokenizer.from_pretrained(model_loc)
prompts = "[INST] {0} [/INST] {1}"
    
global proc
proc = None
def llm_compute(data): 
    global proc
    torch.cuda.empty_cache()
    try:
        s = time.time()
        history = [i['msg'] for i in eval(data.get("input").replace("true","True").replace("false","False"))]
        while len("".join(history)) > limit:
            del history[0]
            if history: del history[0]
        if len(history) != 0:
            history[0] = "<<SYS>>\nYou are a helpful, respectful and honest assistant. Always answer as helpfully as possible, while being safe. Please ensure that your responses are socially unbiased and positive in nature. If a question does not make any sense, or is not factually coherent, explain why instead of answering something not correct. If you don't know the answer to a question, please don't share false information.\n<</SYS>>\n\n" + history[0]
            history.append("")
            history = [prompts.format(history[i], history[i + 1]).strip() for i in range(0, len(history), 2)]
            history = " ".join(history)
            encoding = tokenizer(history, return_tensors='pt', add_special_tokens=False).to(model.device)
            
            streamer = TextIteratorStreamer(tokenizer,skip_prompt=True,timeout=2)
            l = encoding['input_ids'].size(1)
            thread = Thread(target=model.generate, kwargs=dict(input_ids=encoding['input_ids'], streamer=streamer,
                generation_config=GenerationConfig(
                    max_new_tokens=4096,
                    pad_token_id=tokenizer.eos_token_id,
                ),stopping_criteria=StoppingCriteriaList([CustomStoppingCriteria()])),daemon=True)
            thread.start()
            proc = thread
            for i in streamer:
                print(end=i.replace("</s>",""),flush=True)
                yield i.replace("</s>","")
                if not proc: break

            thread.join()
            torch.cuda.empty_cache()
        else:
            yield "Sorry, The input message is too huge!"

    except Exception as e:
        print(e)
    finally:
        torch.cuda.empty_cache()
        app.Ready[0] = True
        print("finished")
def abort():
    global proc
    if proc:
        tmp = proc
        proc = None
        print("aborting...")
        tmp.join()
        print("aborted")
        torch.cuda.empty_cache()
        return "Aborted"
    return "No process to abort"
# model part ends
app.llm_compute = llm_compute
app.abort = abort
start()
