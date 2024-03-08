import inspect
import logging
from typing import List, Any, Callable
from types import GeneratorType
from enum import Enum
from timeit import default_timer

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
    A LlmSafetyGuard object should handle exactly one request-response pair.
    """
    
    # The singleton target cache
    target_cache = get_target_cache()

    def __init__(self, n_max_buffer=100, streaming=True):
        self.buffer = PassageBuffer(n_max_buffer=n_max_buffer, streaming=streaming)
        self.detector = DetectionClient()
        self.seen_msgs = [] # To prevent output duplicated warning messages.
        self.response_accumulator = ''

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

        def wrap(chat_history:List[dict], model_id:str, at_exit:Callable=None, *args, **kwargs):

            # Bypass if can't connect to the detector or the model does not need
            # to be guarded.
            if not self.target_cache.should_guard(model_id) or not self.detector.is_online():
                return func(chat_history=chat_history, model_id=model_id, *args, **kwargs)
            else:
                orig_result = func(chat_history=chat_history, model_id=model_id, *args, **kwargs)
                orig_gen, orig_return_type = self._get_original_generator(orig_result)
                gen = self._guard_impl(
                    chat_history=chat_history,
                    model_id=model_id,
                    orig_gen=orig_gen,
                    at_exit=at_exit,
                )
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

    def pre_filter(
        self,
        chat_history:List[dict],
        model_id:str
    ) -> (bool, str | None):
        """
        Public API to check the user input. The caller is responsible for
        outputting the warning message if necessary, and terminating the session
        if the request is blocked.

        Arguments:
            chat_history, model_id: Same as the description in the "guard" method.
        
        Return: (block, message)
            block: Boolean value indicating whether the session should be terminated.
            message: The warning message. The value "None" means no message need to be output.
        """

        # Bypass if can't connect to the detector or the model does not need
        # to be guarded.
        if not self.target_cache.should_guard(model_id) or not self.detector.is_online():
            return False, None

        safe, action, msg = self.detector.pre_filter(chat_history, model_id)
        logger.debug(f'pre-filter: safe={safe}, action={action}, msg={msg}')
        msg = None if safe or not msg else self._format_warning_message(msg)
        block = not safe and action == ActionEnum.block
        self.seen_msgs.append(msg)

        return block, msg

    def post_filter(
        self,
        chat_history:List[dict],
        model_id:str,
        chunk:str,
        last:bool
    ) -> (str | None, bool, str | None):
        """
        Public API to check the assistant output. The caller is responsible for
        outputting the warning message if necessary, and terminating the session
        if the request is blocked.

        Arguments:
            chat_history, model_id: Same as the description in the "guard" method.
            chunk: The response chunk generated from the assistant. The internal state will accumulate the response chunk automatically.
            last: Boolean value indicating whether the response chunk is the last chunk.
        
        Return: (chunk, block, message)
            chunk: The response chunk that the SafetyGuard generates. Note that the output chunk is not necessarily equal to the input chunk due to the buffering mechanism and overwrite mechanism.
            block: Boolean value indicating whether the session should be terminated.
            message: The warning message. The value "None" means no message need to be output.
        """
        
        # Bypass if can't connect to the detector or the model does not need
        # to be guarded.
        if not self.target_cache.should_guard(model_id) or not self.detector.is_online():
            return chunk, False, None
        
        # Construct the chunk
        self.buffer.append(chunk, last)
        chunk = self.buffer.get_chunk()
        if not chunk:
            return None, False, None

        # Check whether the chunk is safe
        safe, action, msg = self.detector.post_filter(chat_history, self.response_accumulator + chunk, model_id)

        logger.debug(f'post-filter: safe={safe}, action={action}, msg={msg}')
        block = False
        warn_msg = None
        if not safe:
            if action == ActionEnum.overwrite:
                original_len = len(self.response_accumulator)
                chunk = msg[original_len:]
            elif msg and msg not in self.seen_msgs:
                self.seen_msgs.append(msg)
                warn_msg = self._format_warning_message(msg)
            if action == ActionEnum.block:
                block, chunk = True, warn_msg
        self.response_accumulator += chunk

        return chunk, block, warn_msg
    
    def _guard_impl(self, chat_history:List[dict], model_id:str, orig_gen, at_exit:Callable=None):
        """
        A generator that implements the guarding function.
        Arguments:
            chat_history, model_id: Same as the description in the "guard" method.
            orig_gen: The original function to handel the request. The function is
            expected return a generator as the generation result.
            at_exit (optional): The function that will be always called at the end.
        """
        
        # Pre-filter
        block, msg = self.pre_filter(chat_history, model_id)
        if msg: yield msg
        if block:
            if at_exit: at_exit()
            return
        
        # Post-filter
        finished = False
        while not finished:
            # Consume the original generator.
            output, finished = self._get_unread_contents(orig_gen)
            if not output and not finished: continue

            chunk, block, msg = self.post_filter(chat_history, model_id, output, finished)
            if not chunk: continue

            if msg: yield msg
            if block:
                if at_exit: at_exit()
                return

            yield chunk
        if at_exit: at_exit()

    def _get_unread_contents(self, gen: GeneratorType, time_budget_sec=0.05) -> (str, bool):
        """
        Get all content from the generator within the buffer.
        """

        result = ''
        finished = False
        try:
            begin_t = t = default_timer()
            while t-begin_t < time_budget_sec:
                result += next(gen)
                t = default_timer()
                break
        except StopIteration:
            finished = True
        return result, finished

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


