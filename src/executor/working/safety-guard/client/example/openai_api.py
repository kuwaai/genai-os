import uvicorn
import logging
import time
import json
from datetime import datetime
from typing import List
from fastapi import FastAPI
from fastapi.responses import StreamingResponse, JSONResponse
from apscheduler.schedulers.background import BackgroundScheduler

from llm_safety_guard import LlmSafetyGuard

app = FastAPI()
logger = logging.getLogger(__name__)

@app.post('/v1/chat/completions')
async def chat_completion(req: dict):

    """
    Arguments:
    n_max_buffer: Maximum charters allowed to be stored in the streaming-mode buffer before detection.
    streaming: Running mode. Set to True means the output from the original generator is inspected and output at the chunk basis. Set to False means all the output will be buffered before the detection.
    """
    safety_guard = LlmSafetyGuard(n_max_buffer=100, streaming=True)

    result = {}
    if json.loads(req.get('stream', 'false').lower()):
        result = await handle_streaming_req(req, safety_guard)
    else:
        result = await handle_non_streaming_req(req, safety_guard)

    return result

async def handle_non_streaming_req(req: dict, safety_guard:LlmSafetyGuard):

    chat_history = req['messages']
    model = req['model']
    prompt_len = sum([len(msg['content']) for msg in req['messages']])

    # Pre-filter
    pre_filter_block, pre_filter_msg = safety_guard.pre_filter(chat_history=chat_history, model_id=model)

    assistant_response = ''
    post_filter_msg = ''
    if not pre_filter_block:
        assistant_response = chat_history[-1]['content']

        # Post-filter
        assistant_response, _, post_filter_msg = safety_guard.post_filter(
            chat_history=chat_history,
            model_id=model,
            chunk=assistant_response,
            last=True
        )

    # Concat the warning message from both the pre-filter and post-filter.
    assistant_response = str(pre_filter_msg or '') + assistant_response + str(post_filter_msg or '')

    data = {
        "id": "chatcmpl-123",
        "object": "chat.completion",
        "created": int(time.time()),
        "model": model,
        "system_fingerprint": "fp_44709d6fcb",
        "choices": [
            {
                "index": 0,
                "message": {
                    "role": "assistant",
                    "content": assistant_response,
                },
                "logprobs": None,
                "finish_reason": "stop" if not pre_filter_block else "content_filter"
            }
        ],
        "usage": {
            "prompt_tokens": prompt_len,
            "completion_tokens": len(assistant_response),
            "total_tokens": prompt_len+len(assistant_response)
        }
    }

    return JSONResponse(data, safe=False, json_dumps_params={'ensure_ascii': False})

async def handle_streaming_req(req: dict, safety_guard:LlmSafetyGuard):

    chat_history = req['messages']
    model = req['model']

    return StreamingResponse(
        stream_last_message(chat_history, model, safety_guard),
        media_type="text/event-stream"
    )

async def stream_last_message(chat_history: List[dict], model: str, safety_guard:LlmSafetyGuard):
    """
    The example generator that always echo the last message.
    """

    # Pre-filter
    pre_filter_block, pre_filter_msg = safety_guard.pre_filter(chat_history=chat_history, model_id=model)

    if pre_filter_block or pre_filter_msg:
        data = {
            "id": "chatcmpl-123",
            "object": "chat.completion.chunk",
            "created": int(time.time()),
            "model": model,
            "system_fingerprint": "fp_44709d6fcb",
            "choices":[
                {
                    "index": 0,
                    "delta": {
                        "role":"assistant",
                        "content": pre_filter_msg
                    },
                    "logprobs": None,
                    "finish_reason": "content_filter" if pre_filter_block else None
                    }
                ]
            }
        yield f'data: {json.dumps(data, ensure_ascii=False)}\n'

        if pre_filter_block:
            yield 'data: [DONE]'
            return

    assistant_response = chat_history[-1]['content']

    for i, token in enumerate(assistant_response):

        # Post-filter
        chunk, post_filter_block, post_filter_msg = safety_guard.post_filter(
            chat_history=chat_history,
            model_id=model,
            chunk=token,
            last=(i==len(assistant_response)-1)
        )
        if not chunk: continue
        finish_reason = None
        if post_filter_block:
            finish_reason = "content_filter"
        elif i == len(assistant_response)-1:
            finish_reason = "stop"

        data = {
            "id": "chatcmpl-123",
            "object": "chat.completion.chunk",
            "created": int(time.time()),
            "model": model,
            "system_fingerprint": "fp_44709d6fcb",
            "choices":[
                {
                    "index": 0,
                    "delta": {
                        "role":"assistant",
                        "content": chunk + str(post_filter_msg or '')
                    },
                    "logprobs": None,
                    "finish_reason": finish_reason
                    }
                ]
            }

        yield f'data: {json.dumps(data, ensure_ascii=False)}\n'

        if post_filter_block:
            break
    yield 'data: [DONE]'

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
