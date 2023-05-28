# -#- coding: UTF-8 -*-
import time, re, requests, sys, socket, os, torch
import numpy as np
from flask import Flask, request, Response
from flask_sse import ServerSentEventsBlueprint
os.environ["CUDA_VISIBLE_DEVICES"] = "1"
app = Flask(__name__)
app.config["REDIS_URL"] = "redis://localhost:6379/0"
sse = ServerSentEventsBlueprint('sse', __name__)
app.register_blueprint(sse, url_prefix='/')
# -- Configs --
agent_endpoint = "http://localhost:9000/"
LLM_name = "dolly_gpt-j_6b"
# This is the IP that will be stored in Agent, 
# Make sure the IP address here are accessible by Agent
public_ip = "localhost" 
ignore_agent = False
port = None # By choosing None, it'll assign an unused port
dummy = False
# -- Config ends --

if port == None:
    with socket.socket() as s:
        port = s.bind(('', 0)) or s.getsockname()[1]

Ready = [True]
if not dummy:
    # model part
    from transformers import (
        AutoModelForCausalLM,
        AutoTokenizer,
        PreTrainedModel,
        PreTrainedTokenizer
    )
    model = AutoModelForCausalLM.from_pretrained("Corianas/gpt-j-6B-Dolly", device_map="auto", trust_remote_code=True)
    tokenizer = AutoTokenizer.from_pretrained("Corianas/gpt-j-6B-Dolly")
    eng_prompt = """Below is an instruction that describes a task. Write a response that appropriately completes the request.

### Instruction:
{0}

### Response:
"""
    chi_prompt = """Below is an instruction that describes a task. Write a response that appropriately completes the request.

### Instruction:
{0}

### Response:
"""
    endPrompts = ["### End"]
    checklength = max([len(i) for i in endPrompts])
    def process(data):
        try:
            prompt = eng_prompt
            if "zh-TW" in data:
                prompt = chi_prompt
                data = data.replace("zh-TW", "")
            inputs = tokenizer(prompt.format(data), return_tensors="pt").input_ids
            buffer = None
            run = True
            counter = 0
            checker = True
            records = ""
            regexs = [r'(.*\n)\1', r'(.{2,50})\1+', r': *.*\n', r".\n"]
            repeat_limits = [5,5,2, 15]
            repeat_detected = False
            last = ""
            pos = 0
            #tokenPos = len(inputs[0])
            while checker and counter < 600:
                a = time.time()
                outputs = model.generate(inputs.to("cuda:0"), pad_token_id=tokenizer.pad_token_id, eos_token_id=tokenizer.encode("### End")[0],
                                    max_new_tokens=1, top_p=0.92, top_k=0, do_sample=True, no_repeat_ngram_size=7 #temperature=0.1, top_p=0.65, num_beams=4
                )
                print(time.time() - a)
                inputs = outputs
                if counter % 4 == 0:
                    for index in range(len(regexs)):
                        #validate for repeating
                        pattern = re.compile(regexs[index])
                        matches = pattern.findall(records)
                        if matches:
                            most_common_substring = max({match:matches.count(match) for match in matches}.items(), key=lambda x:x[1])[0].strip()
                            times = records.count(most_common_substring)
                            print(most_common_substring, times, repeat_limits[index])
                            if times >= repeat_limits[index] or len(most_common_substring) > 6:
                                print("Repeat detected!\n", records)
                                repeat_detected = True
                                break
                    if repeat_detected: break
                #if buffer == None: buffer = outputs[0, tokenPos:].cpu()
                #else: buffer = torch.cat((buffer,outputs[0, tokenPos:].cpu()))
                if buffer == None: buffer = outputs[0, -1:].cpu()
                else: buffer = torch.cat((buffer,outputs[0, -1:].cpu()))
                #tokenPos = len(outputs[0])
                outputs = tokenizer.decode(buffer)
                while checker and len(outputs[pos:]) > checklength:
                    for i in endPrompts:
                        if outputs[pos:].startswith(i):
                            checker = False
                            break
                    if checker:
                        if last == outputs[pos]:
                            counter += 1
                            yield outputs[pos].encode("utf-8")
                            print(outputs[pos], end="", flush=True)
                            records += outputs[pos]
                            pos+=1
                        last = outputs[pos]
                torch.cuda.empty_cache()
            del inputs
            del outputs
        except Exception as e:
            print(e)
        finally:
            torch.cuda.empty_cache()
            Ready[0] = True
            print("finished")
    # model part ends
else:
    def process(data): 
        try:
            for i in "The crisp morning air tickled my face as I stepped outside. The sun was just starting to rise, casting a warm orange glow over the cityscape. I took a deep breath in, relishing in the freshness of the morning. As I walked down the street, the sounds of cars and chatter filled my ears. I could see people starting to emerge from their homes, ready to start their day.":
                yield i
                time.sleep(0.02)
        except Exception as e:
            print(e)
        finally:
            Ready[0] = True
            print("finished")

@app.route("/", methods=["POST"])
def api():
    if Ready[0]:
        Ready[0] = False
        data = request.form.get("input")
        resp = Response(process(data), mimetype='text/event-stream')
        resp.headers['Content-Type'] = 'text/event-stream; charset=utf-8'
        if data: return resp
        print("I didn't see your input!")
        Ready[0] = True
    return ""
registered = True
response = requests.post(agent_endpoint + "register", data={"name":LLM_name,"endpoint":"http://{0}:{1}/".format(public_ip, port)})
if response.text == "Failed":
    print("Warning, The server failed to register to agent")
    registered = False
    if not ignore_agent:
        print("The program will exit now.")
        sys.exit(0)
else:
    print("Registered")
    
if __name__ == '__main__':
    app.run(port=port, host="0.0.0.0")
    if registered:
        try:
            response = requests.post(agent_endpoint + "unregister", data={"name":LLM_name,"endpoint":"http://{0}:{1}/".format(public_ip, port)})
            if response.text == "Failed":
                print("Warning, Failed to unregister from agent")
        except requests.exceptions.ConnectionError as e:
            print("Warning, Failed to unregister from agent")