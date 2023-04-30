# -#- coding: UTF-8 -*-
import time, re, os, torch
import numpy as np
from flask import Flask, request, Response
from flask_sse import ServerSentEventsBlueprint
os.environ["CUDA_VISIBLE_DEVICES"] = "1"
app = Flask(__name__)
app.config["REDIS_URL"] = "redis://localhost:6379/0"
sse = ServerSentEventsBlueprint('sse', __name__)
app.register_blueprint(sse, url_prefix='/')
dummy = False
if not dummy:
    # model part
    from transformers import (
        AutoModelForCausalLM,
        AutoTokenizer,
        PreTrainedModel,
        PreTrainedTokenizer
    )
    tokenizer = AutoTokenizer.from_pretrained("Corianas/gpt-j-6B-Dolly")
    model = AutoModelForCausalLM.from_pretrained("Corianas/gpt-j-6B-Dolly", device_map="auto", trust_remote_code=True)
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
        torch.cuda.empty_cache()
        print("finished")
    # model part ends
else:
    def process(data): return "Dummy mode on, This is a dummy message"

@app.route("/", methods=["POST"])
def api():
    data = request.form.get("input")
    resp = Response(process(data), mimetype='text/event-stream')
    resp.headers['Content-Type'] = 'text/event-stream; charset=utf-8'
    if data: return resp
    print("I didn't see your input!")
    return ""

if __name__ == '__main__':
    app.run(port=8003, host="0.0.0.0")
