import socket, os
import logging
from base import LLMWorker

logger = logging.getLogger(__name__)

if not app.LLM_name:
    app.LLM_name = "debug"

global proc
proc = None

def llm_compute(data): 
    global proc
    try:
        proc = True
        for i in """你好我是個語言模型很高興認識你...之類的xD
<<<WARNING>>>
這是一個測試警告
這是二個測試警告
<<</WARNING>>>
中途可以輸出警告
<<<WARNING>>>
警告2，嗨
<<</WARNING>>>
輸出文字模擬結束""":
            yield i
            time.sleep(0.1)
            if not proc: break
    except Exception as e:
        logger.error("Error occurs while processing request.")
    finally:
        proc = False
        app.Ready[0] = True
        logger.debug("finished")
def abort():
    global proc
    if proc:
        proc = False
        logger.debug("aborted")
        return "Aborted"
    return "No process to abort"
# model part ends
app.llm_compute = llm_compute
app.abort = abort
start()