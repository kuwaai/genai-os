import inspect
from typing import List, Any
from types import GeneratorType
from enum import Enum
import logging

from .target_cache import get_target_cache
from .buffer import PassageBuffer
from .detection_client import DetectionClient, ActionEnum

logger = logging.getLogger(__name__)

class AcceptedReturnType(Enum):
    unrecognized = 'unrecognized'
    generator = 'generator'
    generator_with_other = 'generator with other'

class LlmSafetyGuard:
    """
    The public API to use the Safety Guard.
    """
    
    # The singleton target cache
    target_cache = get_target_cache()

    def __init__(self, n_max_buffer=100, streaming=True):
        self.buffer = PassageBuffer(n_max_buffer=n_max_buffer, streaming=streaming)
        self.detector = DetectionClient()

    def guard(self, func):
        """
        A decorator to guard the original function.
        The signature of the input function is expected to have following named
        parameters.
            - "chat_history" containing a list of chat record indicating the chat history;
            - "model_id" containing the identity of the model to be called.
        Moreover, the function is expected return a generator as the generation
        result.
        """

        def wrap(chat_history:List[dict], model_id:str, *args, **kwargs):

            # Bypass if can't connect to the detector or the model does not need
            # to be guarded.
            if not self.target_cache.should_guard(model_id) or not self.detector.is_online():
                return func(chat_history=chat_history, model_id=model_id, *args, **kwargs)
            else:
                orig_result = func(chat_history=chat_history, model_id=model_id, *args, **kwargs)
                orig_gen, orig_return_type = self._get_original_generator(orig_result)
                gen = self._guard_impl(chat_history=chat_history, model_id=model_id, orig_gen=orig_gen)
                if orig_return_type == AcceptedReturnType.generator:
                    return gen
                if orig_return_type == AcceptedReturnType.generator_with_other:
                    return tuple([gen, *orig_result[1:]])
        
        wrap.__signature__ = inspect.signature(func)
        return wrap

    @staticmethod
    def update():
        """
        The exposed API to update the internal state.
        """

        LlmSafetyGuard.target_cache.update_list()
    
    def _guard_impl(self, chat_history:str, model_id:str, orig_gen):
        """
        A generator that implements the guarding function.
        Arguments:
            chat_history, model_id: Same as the description in the "guard" method.
            orig_gen: The original function to handel the request. The function is
            expected return a generator as the generation result.
        """
        
        # Pre-filter
        safe, action, msg = self.detector.pre_filter(chat_history, model_id)
        logger.debug(f'pre-filter: safe={safe}, action={action}, msg={msg}')
        if not safe:
            if msg: yield self._format_warning_message(msg)
            if action == ActionEnum.block: return

        # Post-filter
        seen_msgs = [msg] # To prevent output duplicated warning messages.
        finished = False
        response = ''
        while not finished:
            try:
                output = next(orig_gen)
            except StopIteration:
                finished = True
            
            # Construct the chunk
            if not finished:
                self.buffer.append(output)
            else:
                self.buffer.finalize()
            chunk = self.buffer.get_chunk()
            if not chunk: continue

            # Check whether the chunk is safe
            response += chunk
            safe, action, msg = self.detector.post_filter(chat_history, response, model_id)
            logger.debug(f'post-filter: safe={safe}, action={action}, msg={msg}')
            if not safe:
                if action == ActionEnum.overwrite:
                    chunk = msg
                elif msg and msg not in seen_msgs:
                    seen_msgs.append(msg)
                    yield self._format_warning_message(msg)
                if action == ActionEnum.block:
                    return

            yield chunk

    @staticmethod
    def _format_warning_message(msg:str):
        return f'<<<WARNING>>>{msg}<<</WARNING>>>'
    
    @staticmethod
    def _get_original_generator(orig_result:Any):
        orig_gen = None
        return_type = AcceptedReturnType.unrecognized
        if isinstance(orig_result, GeneratorType):
            orig_gen = orig_result
            return_type = AcceptedReturnType.generator
        elif isinstance(orig_result, tuple) and isinstance(orig_result[0], GeneratorType):
            orig_gen = orig_result[0]
            return_type = AcceptedReturnType.generator_with_other
        else:
            raise ValueError(f'Unrecognized return type {type(orig_result)} of the original processing function.')
        return orig_gen, return_type


