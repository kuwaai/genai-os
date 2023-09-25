# -#- coding: UTF-8 -*-
import time, re, requests, sys, socket, os, torch
from flask import Flask, request, Response
from flask_sse import ServerSentEventsBlueprint
os.environ["CUDA_VISIBLE_DEVICES"] = "0"
app = Flask(__name__)
app.config["REDIS_URL"] = "redis://192.168.211.4:6379/0"
sse = ServerSentEventsBlueprint('sse', __name__)
app.register_blueprint(sse, url_prefix='/')
# -- Configs --
agent_endpoint = "http://192.168.211.4:9000/"
LLM_name = "llama2-7b-chat-b1.0.0-tc"
# This is the IP that will be stored in Agent,
# Make sure the IP address here are accessible by Agent
public_ip = None
if public_ip = None: public_ip = socket.gethostbyname(socket.gethostname())
ignore_agent = False
port = None # By choosing None, it'll assign an unused port
dummy = False
api_key = "uwU123DisApikEyiSASeCRetheHehee"
usr_token = "92d1e9d60879348b8ed2f25f624012dcc596808dc40681d74c4965b8fff8a22a"
tc_model = 26
# -- Config ends --

if port == None:
    with socket.socket() as s:
        port = s.bind(('', 0)) or s.getsockname()[1]

Ready = [True]
if not dummy:
    # model part
    from transformers import LlamaForCausalLM, LlamaTokenizer, GenerationConfig
    model = LlamaForCausalLM.from_pretrained("llama2-7b-chat-b1.0.0", device_map="auto",torch_dtype=torch.float16)
    tokenizer = LlamaTokenizer.from_pretrained("llama2-7b-chat-b1.0.0", add_bos_token=False)
    BOS = tokenizer.bos_token
    EOS = tokenizer.eos_token
    endPrompts = [EOS]
    prompts = BOS + "Human\n" + "{0}" + EOS + "\n" + BOS + "Assistant\n"
    checklength = max([len(i) for i in endPrompts])
    def process(data):
        try:
            tc_trans = False
            run = True
            counter = 0
            checker = True
            records = ""
            prompt = data.strip()
            segmenter = tokenizer.eos_token
            inputs = tokenizer.encode(prompts.format(prompt), return_tensors='pt')
            repeat_detected = False
            last = ""
            pos = len(tokenizer.decode(inputs[0].cpu(), skip_special_tokens=False))
            tokenPos = len(inputs[0])
            while checker and len(records) < 600:
                a = time.time()
                outputs = model.generate(
                    inputs.to("cuda:0"), max_new_tokens=2048, generation_config=
                    GenerationConfig(
                        top_p=0.92, top_k=0, do_sample=True, no_repeat_ngram_size=7
                        ,temperature=0.2,repetition_penalty = 1.0,
                        #top_p=0.65,
                        #num_beams=4,
                        #no_repeat_ngram_size=7,
                    )
                )
                print(time.time() - a)
                inputs = outputs
                tokenPos = len(outputs[0])
                outputs = tokenizer.decode(outputs[0].cpu(), skip_special_tokens=False).strip()

                if len(outputs[pos:]) >= checklength:
                    res = requests.get("https://chatdev.gai.tw/api_auth?key={0}&api_token={1}&llm_id={2}&msg={3}".format(api_key, usr_token, tc_model, outputs))
                    if res.status_code == 200:
                        res = res.json()
                        if res["status"] == "success":
                            print("Before trans:", outputs)
                            outputs = res["output"]
                            print("After trans:")
                            tc_trans = True
                            pos = outputs.index("<s>Assistant\n") + len("<s>Assistant\n") + len(records)
                while checker and len(outputs[pos:]) >= checklength:
                    for i in endPrompts:
                        if outputs[pos:].startswith(i):
                            checker = False
                            break
                    if checker:
                        counter += 1
                        time.sleep(0.02)
                        if outputs[pos:].startswith("\\n"):
                            yield "\n"
                            pos += len("\\n")-1
                            print("\n", end="", flush=True)
                        else:
                            yield outputs[pos].encode("utf-8")
                            print(outputs[pos], end="", flush=True)

                        records += outputs[pos]
                        pos+=len(outputs[pos])

                torch.cuda.empty_cache()
            if tc_trans:
                yield "\n\n[本訊息經過繁體翻譯]".encode("utf-8")
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
response = requests.post(agent_endpoint + "register", data={"name":LLM_name,"port":port})
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
            response = requests.post(agent_endpoint + "unregister", data={"name":LLM_name,"port":port})
            if response.text == "Failed":
                print("Warning, Failed to unregister from agent")
        except requests.exceptions.ConnectionError as e:
            print("Warning, Failed to unregister from agent")
