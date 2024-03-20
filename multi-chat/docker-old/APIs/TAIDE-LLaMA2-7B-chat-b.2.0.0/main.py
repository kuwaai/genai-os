import socket, os
from threading import Thread
import torch

import sys
sys.path.append('../')
from base import *
sys.path.remove('../')

# -- Configs --
app.config["REDIS_URL"] = "redis://redis:6379/0"
app.agent_endpoint = "http://web:9000/"
app.LLM_name = "taide2_7b_chat_b2"
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
model_loc = "llama2-7b-chat-ccw_cp-j-v2+cc_ft-k.3-e3_tv"
tokenizer_loc = "llama2-7b-chat-ccw_cp-j-v2+cc_ft-k.3-e3_tv"
api_key = None
usr_token = None
tc_model = None
# -- Config ends --
# -- Model Part --
# Model Setting
# model part
from transformers import AutoTokenizer, AutoModelForCausalLM, GenerationConfig, TextIteratorStreamer
model = AutoModelForCausalLM.from_pretrained(model_loc, device_map="auto",torch_dtype=torch.float16)
tokenizer = AutoTokenizer.from_pretrained(tokenizer_loc, add_bos_token=False)
generation_config = GenerationConfig(
    temperature= 0.2, 
    top_p=0.92, 
    top_k=0, 
    do_sample=True, 
    no_repeat_ngram_size=7,
    repetition_penalty = 1.0, 
)
system_prompt_fmt = "<<SYS>>\n{0}\n<</SYS>>\n\n {1}"
system_text = "You are a helpful assistant. 你是一個樂於助人的助手。"
prompt_fmt = "<s>[INST] {0} [/INST]\n"
answer_fmt = " {0} </s>"

def process(data):
    try:
        history = [i['msg'] for i in eval(data.get("input").replace("true","True").replace("false","False"))]
        while len("".join(history)) > limit:
            del history[0]
            del history[0]
        if len(history) != 0:
            history[0] = system_prompt_fmt.format(system_text, history[0])
            history_process = []
            for i in range(0, len(history), 2):
                tmp_txt = ""
                if i == (len(history)-1):
                    tmp_txt = prompt_fmt.format(history[i])
                else:
                    tmp_txt = prompt_fmt.format(history[i])+answer_fmt.format(history[i+1])
                history_process.append(tmp_txt)
            prompt = "".join(history_process)
            print(prompt.encode('utf-8','ignore').decode('utf-8'))
            input_ids = tokenizer.encode(prompt, return_tensors='pt').to("cuda:0")
            streamer =TextIteratorStreamer(tokenizer, skip_prompt=True, skip_special_tokens=True)
            generation_kwargs = dict(input_ids=input_ids, streamer=streamer, max_new_tokens=2048,generation_config=generation_config)
            thread = Thread(target=model.generate, kwargs=generation_kwargs)
            thread.start()
            generated_text = ""
            for new_text in streamer:
                # if "</s>" in new_text:
                #     new_text = new_text.replace("</s>","")
                yield new_text
                generated_text += new_text
                torch.cuda.empty_cache()
            del streamer
            print(generated_text.encode('utf-8','ignore').decode('utf-8'))
        else:
            yield "Sorry, The input message is too huge!"

    except Exception as e:
        print(e)
    finally:
        torch.cuda.empty_cache()
        app.Ready[0] = True
        print("finished")
# model part ends
app.llm_compute = process
start()