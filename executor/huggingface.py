import socket, os
from base import *
from transformers import AutoTokenizer, GenerationConfig, TextIteratorStreamer, StoppingCriteria, StoppingCriteriaList
from intel_extension_for_transformers.transformers import AutoModelForCausalLM
from threading import Thread

if not app.LLM_name:
    raise Exception("You need to configure an Access_code!")
if not app.model_path:
    raise Exception("You need to configure a model path!")
    
class CustomStoppingCriteria(StoppingCriteria):
    def __init__(self):
        pass

    def __call__(self, input_ids, score, **kwargs) -> bool:
        global proc
        return not proc

model = AutoModelForCausalLM.from_pretrained(model_loc, device_map="auto", torch_dtype=torch.float16)
tokenizer = AutoTokenizer.from_pretrained(model_loc)
prompts = " USER: {0} ASSISTANT: {1}"
stopwords = ["USER:", "</s>"]
bufferLength = max([len(k) for k in stopwords]) if stopwords else -1

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
            history.append("")
            history = [prompts.format(history[i], history[i + 1]) for i in range(0, len(history), 2)]
            history[0] = "<s>" + history[0]
            history = "".join(history).strip()
            encoding = tokenizer(history, return_tensors='pt', add_special_tokens=False).to(model.device)
            
            streamer = TextIteratorStreamer(tokenizer,skip_prompt=True,timeout=2)
            l = encoding['input_ids'].size(1)
            thread = Thread(target=model.generate, kwargs=dict(input_ids=encoding['input_ids'], streamer=streamer,
                generation_config=GenerationConfig(
                    max_new_tokens=4096,
                    do_sample=False,
                    repetition_penalty = 1.0
                ),stopping_criteria=StoppingCriteriaList([CustomStoppingCriteria()])),daemon=True)
            thread.start()
            proc = thread
            buffer = ""
            if bufferLength: print("buffering with", bufferLength, "length")
            for i in streamer:
                if bufferLength != -1:
                    buffer += i
                    for o in stopwords:
                        if o in buffer: 
                            proc = None
                            print(o,"founded!")
                            buffer = buffer.split(o)[0]
                            break
                    if len(buffer) > bufferLength:
                        print(end=buffer[0],flush=True)
                        yield buffer[0]
                        buffer = buffer[1:]
                    if not proc:
                        print(end=buffer,flush=True)
                        yield buffer # clear buffer
                else:
                    print(end=i.replace("</s>",""),flush=True)
                    yield i.replace("</s>","")
                if not proc: break
            thread.join()
            torch.cuda.empty_cache()
        else:
            yield "[Sorry, The input message is too long!]"
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
