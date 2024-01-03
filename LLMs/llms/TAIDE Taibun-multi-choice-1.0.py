import socket, os
from base import *

# -- Configs --
app.config["REDIS_URL"] = "redis://192.168.211.4:6379/0"
os.environ["CUDA_VISIBLE_DEVICES"] = "1"
app.agent_endpoint = "http://192.168.211.4:9000/"
app.LLM_name = "Taibun-multi-choice-1.0"
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
model_loc = "./Taibun-multi-choice-1.0"
api_key = None
usr_token = None
tc_model = None
# -- Config ends --

from transformers import AutoModelForCausalLM, AutoTokenizer, GenerationConfig, set_seed
    
#set_seed(42)
model = AutoModelForCausalLM.from_pretrained(model_loc,device_map="auto", torch_dtype=torch.float16)
tokenizer = AutoTokenizer.from_pretrained(model_loc)
prompts = "<s>[INST] {0} [/INST]\n{1}"
    
def llm_compute(data): 
    try:
        history = [i['msg'] for i in eval(data.get("input").replace("true","True").replace("false","False"))]
        if len(history) != 0:
            history = history[-1]
            history = "[INST] <<SYS>>\n你是一個樂於助人的助手。\n<</SYS>>\n\n{0} [/INST]".format(history)
            encoding = tokenizer(history, return_tensors='pt', add_special_tokens=False).to(model.device)
            
            l = encoding['input_ids'].size(1)
            x = model.generate(
                **encoding,
                generation_config=GenerationConfig(
                    max_new_tokens=16,
                    pad_token_id=tokenizer.eos_token_id,
top_p=0.9,
top_k=40,
temperature=0.2,
do_sample=True,
repetition_penalty=1.1,
guidance_scale=1.0,
presence_penalty=0.0
                )
            )
            print( tokenizer.batch_decode(x[:])[0])
            result = tokenizer.batch_decode(x[:, l:])[0].strip().replace("</s>","")
            #print(tokenizer.batch_decode(x)[0].encode("utf-8","ignore").decode("utf-8"))
            for i in result:
                yield i
                time.sleep(0.02)

            torch.cuda.empty_cache()
        else:
            yield "[Sorry, The input message is too long!]"

    except Exception as e:
        print(e)
    finally:
        torch.cuda.empty_cache()
        app.Ready[0] = True
        print("finished")
# model part ends
app.llm_compute = llm_compute
start()
