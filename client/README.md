Safety Guard Client
===

This is the client library of the safety guard system. The library is expected
to be integrated into the proxy server on the primary prompt/response path.

## Usage
1. Set the following environment variable before importing the Safety Guard client library
```shell
# variable=default
SAFETY_GUARD_MANAGER_URL=http://localhost:8000
SAFETY_GUARD_DETECTOR_URL=grpc://localhost:50051
```
2. Install the client library of the Safety Guard
```shell
pip install -e .
```
3. Apply the decorator as a middleware to the processing function.
The processing function is expected to have the following signature.
An adaptor may be needed to convert the signature.
```python
import inspect
from fastapi import FastAPI
from llm_safety_guard import LlmSafetyGuard


app = FastAPI()

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
        The format of the chat record is {'role': 'user' or 'assistant', 'msg': 'the content of the message'};
        """
        chat_history = [
            {'role': m['role'], 'msg': m['content']}
            for m in req['messages']
        ]

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
    
    def generator():
        """
        The example generator that always return the last message.
        """
        for token in chat_history[-1]['msg']:
            yield token

    # Two return type are accepted: (1) A generator; (2) A tuple which the first element is a generator.
    # 
    # return generator, {'Content-Type': 'text/plain'}
    # or
    return generator

```