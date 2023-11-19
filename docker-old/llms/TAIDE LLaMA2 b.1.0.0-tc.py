import socket, os
from base import *

# -- Configs --
app.config["REDIS_URL"] = "redis://192.168.211.4:6379/0"
os.environ["CUDA_VISIBLE_DEVICES"] = "0"
app.agent_endpoint = "http://192.168.211.4:9000/"
app.LLM_name = "llama2-7b-chat-b1.0.0-tc"
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
usr_token = "92d1e9d60879348b8ed2f25f624012dcc596808dc40681d74c4965b8fff8a22a"
# -- Config ends --
import opencc
from ckip_transformers.nlp import CkipWordSegmenter
from functools import reduce
# import hanzidentifier
import cjieba

def is_code(text: str, code: str):
  iconv = lambda t, c: t.encode(c, errors='ignore').decode(c)
  return len(text) == len(iconv(text, code))
class JiebaWordSegmenter:
  def __init__(self):
    pass
  
  def __call__(self, input_text: [str], **kwargs) -> [[str]]:
    return list(map(cjieba.cut, input_text))

from dataclasses import dataclass
from enum import Enum
class Role(Enum):
    USER = 'user'
    SYS  = 'system'
    BOT  = 'bot'

    def __str__(self):
        return str(self.value)
@dataclass
class ChatRecord:
  msg: str   # Message.
  role: Role # Who said this.
class TextLevelFilteringInterface:
  def filter(self, msg: [ChatRecord]) -> [ChatRecord]:
    pass
    
tw2sp_config_file = "tw2sp.json"
if not os.path.exists(tw2sp_config_file):
    print(f"Alert: Config file '{tw2sp_config_file}' not found!")

class ContextualCC(TextLevelFilteringInterface):
  def __init__(self, dst_region='tw'):
    if dst_region == 'tw':
      # self.is_dst_code = lambda t: not hanzidentifier.is_simplified(t)
      self.is_dst_code = lambda t: is_code(t, 'big5') and not is_code(t, 'gb2312')
      opencc_config = 's2twp.json'
      self.ws_driver = JiebaWordSegmenter()

    elif dst_region == 'cn':
      # self.is_dst_code = lambda t: not hanzidentifier.is_traditional(t)
      self.is_dst_code = lambda t: is_code(t, 'gb2312') and not is_code(t, 'big5')
      opencc_config = 'tw2sp.json'
      self.ws_driver = CkipWordSegmenter(model="albert-tiny")
      
    else:
      raise ValueError('Unsupported destination region.') 

    self.converter = opencc.OpenCC(opencc_config)

  def convert(self, text:str):
    """
    Convert the text only if it contains unrecognized charter 
    """
    if self.is_dst_code(text):
      return text
    else:
      return self.converter.convert(text)

  def filter(self, records: [ChatRecord]) -> [ChatRecord]:

    result = []

    for record in records:

      text = record.msg
      if self.is_dst_code(text):
        result.append(record)
      else:
        # The segmenter work better on Traditional Chinese
        words = self.ws_driver(input_text=[text], show_progress=False)[0]
        converted_text = reduce(lambda sum, t: sum+self.convert(t), words, '')

        result.append(ChatRecord(converted_text, record.role))

    return result
def llm_compute(data): 
    try:
        if data.get("input"):
            url = "https://chatdev.gai.tw/v1.0/chat/completions"

            headers = {
                "Content-Type": "application/json",
                "Authorization": f"Bearer {usr_token}",
            }

            data1 = {
                "messages": [{"msg":i["msg"].replace("\n\n[本訊息經過繁體翻譯]",""), "isbot":i["isbot"]} for i in eval(data.get("input").replace("true","True").replace("false","False"))],
                "model": model_loc
            }
            res = requests.post(url, headers=headers, json=data1)
            result = ""
            if res.status_code == 200:
                res = res.json()
                if res["status"] == "success":
                    result = res["output"]
                    cc = ContextualCC(dst_region='tw')
                    chat_records = [
                        ChatRecord(result, Role.USER)
                    ]
                    filtered_records = cc.filter(chat_records)
                    result = filtered_records[0].msg + "\n\n[本訊息經過繁體翻譯]"
                else:
                    print("Failed to auth API!", res)
            else:
                print("Calling API failed", res.status_code)
            if result:
                for i in result:
                    yield i
                    print(end=i)
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
