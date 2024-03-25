import socket, os
from base import *
from llama_cpp import Llama
    
if not app.model_path:
    raise Exception("You need to configure a model path!")
    
model = Llama(model_path=app.model_path)
prompts = "<s>[INST] {0} [/INST]{1}"
    
def llm_compute(data): 
    try:
        s = time.time()
        history = [i['msg'] for i in eval(data.get("input").replace("true","True").replace("false","False"))]
        while len("".join(history)) > app.limit:
            del history[0]
            if history: del history[0]
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
            yield "[Sorry, The input message is too long!]"

    except Exception as e:
        print(e)
    finally:
        app.Ready[0] = True
        print("finished")
# model part ends
app.llm_compute = llm_compute
start()
