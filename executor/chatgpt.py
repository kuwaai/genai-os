import socket, os, openai
from base import *

if not app.LLM_name:
    app.LLM_name = "chatgpt"

global proc
proc = False
def llm_compute(data): 
    global proc
    try:
        chatgpt_apitoken = data.get("chatgpt_apitoken")
        msg = [{"content":i['msg'], "role":"assistant" if i['isbot'] else "user"} for i in eval(data.get("input").replace("true","True").replace("false","False"))]
        
        if msg and chatgpt_apitoken:
            chatgpt_apitoken = chatgpt_apitoken.strip()
            if len(msg) > 0 and len(chatgpt_apitoken) > 0:
                openai.api_key = chatgpt_apitoken
                proc = True
                limit = 1000*3+512 - len(str(msg))
                if limit < 256: limit = 1000*16 - len(str(msg))
                if limit <= 1000*3+512:
                    for i in openai.chat.completions.create(model="gpt-3.5-turbo",
                          max_tokens=limit,
                          temperature=0.5,
                          messages=msg, stream=True):
                        if i.choices[0].delta.content:
                            if ("This model's maximum context length is" in i.choices[0].delta.content):
                                limit = 1000*16 - len(str(msg))
                                break
                            print(end=i.choices[0].delta.content)
                            yield i.choices[0].delta.content
                        if not proc: break
                if limit > 1000*3+512:
                    for i in openai.chat.completions.create(model="gpt-3.5-turbo-16k",
                          max_tokens=limit,
                          temperature=0.5,
                          messages=msg, stream=True):
                        if i.choices[0].delta.content:
                            yield i.choices[0].delta.content
                        if not proc: break
                print(limit)
                openai.api_key = None
            else:
                yield "[請在網站的使用者設定中，將您的OpenAI API Token填入，才能使用該模型]" if len(msg) > 0 else "[沒有輸入任何訊息]"
        else:
            yield "[請在網站的使用者設定中，將您的OpenAI API Token填入，才能使用該模型]" if msg else "[沒有輸入任何訊息]"
    except Exception as e:
        print(e)
        if str(e).startswith("Incorrect API key provided:"):
            yield "[無效的OpenAI API Token，請檢查該OpenAI API Token是否正確]"
        else:
            yield str(e)
    finally:
        proc = False
        app.Ready[0] = True
        print("finished")
        
def abort():
    global proc
    if proc:
        proc = False
        print("aborted")
        torch.cuda.empty_cache()
        return "Aborted"
    return "No process to abort"
app.llm_compute = llm_compute
app.abort = abort
start()
