import uvicorn
import logging
from datetime import datetime
from typing import List
from fastapi import FastAPI
from apscheduler.schedulers.background import BackgroundScheduler

from llm_safety_guard import LlmSafetyGuard

app = FastAPI()
logger = logging.getLogger(__name__)

def at_exit():
    print('Generation done')

def safety_middleware(func):
    """
    The decorator to apply the functionality of the Safety Guard.
    """

    def wrap(req: dict):
        nonlocal func
        
        """
        Arguments:
        n_max_buffer: Maximum charters allowed to be stored in the streaming-mode buffer before detection.
        streaming: Running mode. Set to True means the output from the original generator is inspected and output at the chunk basis. Set to False means all the output will be buffered before the detection.
        """
        safety_guard = LlmSafetyGuard(n_max_buffer=100, streaming=True)

        guarded_func = safety_guard.guard(func)

        """
        Convert the chat history to the safety guard accepted format.
        The format of the chat record is {'role': 'user' or 'assistant', 'content': 'the content of the message'};
        """
        chat_history = req['messages']

        """
        The decorated function can accept an optional argument "at_exit" that specifies a function to be called at the end of a generation or terminated session.
        Moreover, the return type of the safety guard is the same as the original function.
        """
        generator = guarded_func(
            chat_history=chat_history,
            model_id=req['model'],
            at_exit=at_exit,
            req=req
        )

        return generator

    return wrap

def safety_guard_adaptor(func):
    """
    The adaptor to convert the function signature.
    """
    def wrap(chat_history:List[dict], model_id:str, *args, **kwargs):
        """
        Parameters:
        chat_history: containing a list of chat record indicating the chat history.
        model_id: containing the identity of the model to be called.
        """

        return func(req=kwargs['req'])

    return wrap

@app.post('/v1/chat/completions')
@safety_middleware
@safety_guard_adaptor
def chat_completion(req: dict):
    
    chat_history = req['messages']

    def generator(chat_history: List[dict]):
        """
        The example generator that always echo the last message.
        """
        for token in chat_history[-1]['content']:
            yield token

    # Two return type are accepted: (1) A generator; (2) A tuple which the first element is a generator.
    # 
    # return generator, {'Content-Type': 'text/plain'}
    # or
    return generator(chat_history)

if __name__ == "__main__":
    logging.basicConfig(level=logging.DEBUG)

    safety_guard_update_interval_sec = 30
    scheduler = BackgroundScheduler()
    scheduler.add_job(
        func=LlmSafetyGuard.update,
        trigger="interval",
        seconds=safety_guard_update_interval_sec,
        next_run_time=datetime.now()
    )
    scheduler.start()
    uvicorn.run(app, host="127.0.0.1", port=8080)