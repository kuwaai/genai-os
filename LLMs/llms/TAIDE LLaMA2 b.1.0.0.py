import socket, os
from base import *

# -- Configs --
app.config["REDIS_URL"] = "redis://192.168.211.4:6379/0"
os.environ["CUDA_VISIBLE_DEVICES"] = "0"
app.agent_endpoint = "http://192.168.211.4:9000/"
app.LLM_name = "llama2-7b-chat-b1.0.0"
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
model_loc = "llama2-7b-chat-b1.0.0"
api_key = None
usr_token = None
tc_model = None
# -- Config ends --

from transformers import AutoModelForCausalLM, AutoConfig, AutoTokenizer, StoppingCriteria, StoppingCriteriaList, pipeline
    
class StopOnTokens(StoppingCriteria):
    def __call__(self, input_ids: torch.LongTensor, scores: torch.FloatTensor, **kwargs) -> bool:
        for stop_ids in stop_token_ids:
            if torch.all(input_ids[0][-len(stop_ids):] == stop_ids):
                return True
        return False


model = AutoModelForCausalLM.from_pretrained(model_loc,
    config=AutoConfig.from_pretrained(model_loc),device_map="auto",torch_dtype=torch.float16)
model.eval()
tokenizer = AutoTokenizer.from_pretrained(model_loc)
stop_list = ['[INST]', '\nQuestion:', "[INST: ]"]
stop_token_ids = [torch.LongTensor(tokenizer(x)['input_ids']).to('cuda') for x in stop_list]
pipe = pipeline(model=model, tokenizer=tokenizer,return_full_text=True,
    task='text-generation',stopping_criteria=StoppingCriteriaList([StopOnTokens()]),
    temperature=0.2,max_new_tokens=2048,repetition_penalty = 1.0, do_sample=True)
prompts = "<s>[INST] {0} [/INST]\n {1}"
    
def llm_compute(data): 
    try:
        history = [i['msg'] for i in eval(data.get("input").replace("true","True").replace("false","False"))]
        while len(history) > limit: del history[0]
        history[0] = "<<SYS>>\n你的名子是 TAIDE, 你是個能夠理解使用者的大語言模型AI，能流暢的以繁體中文溝通，能專業且流利的回答使用者，專長在文本翻譯、寫文章、寫信、自動摘要上\n<</SYS>>\n\n" + history[0]
        history.append("")
        history = [prompts.format(history[i], ("{0}" if i+1 == len(history) - 2 else " {0} </s>").format(history[i + 1])) for i in range(0, len(history), 2)]
        history = "".join(history)
        result = pipe(history)[0]['generated_text']
        print(result)
        for i in result[len(history):]:
            yield i
            time.sleep(0.02)

        torch.cuda.empty_cache()

    except Exception as e:
        print(e)
    finally:
        torch.cuda.empty_cache()
        app.Ready[0] = True
        print("finished")
# model part ends
app.llm_compute = llm_compute
start()