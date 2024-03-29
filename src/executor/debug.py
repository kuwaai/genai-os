import socket, os, time
from base import LLMWorker

logger = logging.getLogger(__name__)

if not app.LLM_name:
    app.LLM_name = "debug"

def llm_compute(data): 
    try:
        for i in "".join([i['msg'] for i in eval(data.get("input").replace("true","True").replace("false","False"))]).strip():
            yield i
            time.sleep(0.02)
    except Exception as e:
        logger.exception()
    finally:
        app.Ready[0] = True
        logger.info("finished")
app.llm_compute = llm_compute
start()