import logging
import inspect
import json
from typing import List

from .functions import log

def safety_middleware(func):
    bypass = True
    try:
        from llm_safety_guard import LlmSafetyGuard
        bypass = False
    except ImportError:
        logging.exception('Bypassing')
        log(0, 'Bypassing safety middleware due to the package "llm-safety-guard" is not installed.')

    def wrap(*args, **kwargs):
        nonlocal func
        if bypass:
            return func(*args, **kwargs)
        
        safety_guard = LlmSafetyGuard(n_max_buffer=50, streaming=True)
        func = to_safety_guard_signature(func)
        func = safety_guard.guard(func)
        func = to_completions_backend_signature(func)
        return func(*args, **kwargs)
    
    wrap.__signature__ = inspect.signature(func)
    return wrap

def to_safety_guard_signature(func):
    """
    Convert the function signature to the llm-safety-guard compatible one.
    """

    def wrap(chat_history:List[dict], model_id:str, *args, **kwargs):
        chat_history = [
            {'isbot': r['role']=='assistant', 'msg': r['content']}
            for r in chat_history
        ]
        return func(inputs=json.dumps(chat_history), llm_name=model_id, *args, **kwargs)
    return wrap

def to_completions_backend_signature(func):
    """
    Convert the function signature to the completions_backend() compatible one.
    """

    def wrap(inputs:List[dict], llm_name:str, *args, **kwargs):
        if isinstance(inputs, str):
            inputs = json.loads(inputs)
        inputs = [
            {'role': 'assistant' if r['isbot'] else 'user', 'content': r['msg']}
            for r in inputs
        ]
        return func(chat_history=inputs, model_id=llm_name, *args, **kwargs)
    return wrap

def update_safety_guard():
    try:
        from llm_safety_guard import get_target_cache
        get_target_cache().update_list()
    except ImportError:
        pass
