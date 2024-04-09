import socket, os, requests, json, sys
sys.path.append('../')
from base import *
sys.path.remove('../')
from dotenv import load_dotenv
load_dotenv()

# -- Configs --
app.agent_endpoint = "http://web:9000/"
app.LLM_name = os.getenv("MODEL_NAME")
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
limit = 1024*4
model_loc = os.getenv("MODEL_NAME")
api_key = None
usr_token = None
tc_model = None
api_host = os.getenv("API_HOST")
token = os.getenv("TOKEN")
headers = {
  "Authorization": "Bearer "+token,
  'Content-type': 'application/json'
}

# -- Config ends --
system_prompt = os.getenv("SYSTEM_PROMPT")
prompts = os.getenv("PROMPTS")

global proc
proc = False
def llm_compute(data): 
    global proc
    try:
        s = time.time()
        history = [i['msg'] for i in eval(data.get("input").replace("true","True").replace("false","False"))]
        while len("".join(history)) > limit:
            del history[0]
            if history: del history[0]
        if len(history) != 0:
            history.append("")
            history = [prompts.format(history[i], history[i + 1]) for i in range(0, len(history), 2)]
            history[0] = system_prompt + history[0]
            history = "".join(history).strip()
            print(f"Prompt:\n{history.encode('utf-8','ignore').decode('utf-8')}")
            prompt_1 = history
            data = {
                "model": model_loc,
                "prompt": prompt_1,
                "temperature": 0,
                "max_tokens": int(limit-len("".join(history))), 
                "stream":True
                }
            proc = True
            r = requests.post(api_host+"/completions", json=data, headers=headers, stream=True)
            for line in r.iter_lines():
                decoded_line = line.decode('utf-8')
                if decoded_line and "[DONE]" not in decoded_line:
                    line_fixed = decoded_line.replace('data: ', '')
                    data_dict = json.loads(line_fixed)
                    # print(data_dict["choices"][0]["text"])
                    yield data_dict["choices"][0]["text"]
                if not proc: break
        else:
            yield "[Sorry, The input message is too long!]"
    except Exception as e:
        print(e)
    finally:
        proc = False
        app.Ready[0] = True
        print("finished")
        
def abort():
    global proc
    if proc:
        proc = False
        print("aborted")
        return "Aborted"
    return "No process to abort"
app.llm_compute = llm_compute
app.abort = abort
start()
