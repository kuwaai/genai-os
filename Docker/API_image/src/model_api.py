#!/bin/python3
# -#- coding: UTF-8 -*-

import sys, os, time, logging, atexit
import requests, socket
from urllib.parse import urljoin

import asyncio
import uvicorn
from starlette.applications import Starlette
from starlette.routing import Route
from sse_starlette.sse import EventSourceResponse
from starlette.responses import JSONResponse

from models.reflect import ReflectModel
from filters.chinese_translate import OpenCC

# The default value of configuration.
# The value can be override by specifying environment variables.
CONFIG = {
    'agent_endpoint': 'http://localhost:9000/', # The root endpoint of the Agent.
    'LLM_name': 'Unnamed_LLM', # The name of this model.
    'public_ip': 'localhost', # The address that can be accessed by the Agent.
    'port': None, # The public port number for this API. Leave it as None to have it assigned by the system.
    'endpoint': '/v1/completion', # The endpoint of this Model API to serve external requests.
    'ignore_agent': False, # Continue running regardless of whether register successfully with the Agent.
    'logging_level': logging.INFO, # The log above this level will be display
    'retry_count': 5, # How may time should the API server try to register to the Agent
}

# Global state to indicate whether the model is processing another request.
BUSY = False

# Logger of this module
logger = logging.getLogger(__name__) 
logger.addHandler(logging.StreamHandler())

def assign_unused_port():
    sock = socket.socket()
    sock.bind(('', 0))
    port = sock.getsockname()[1]
    sock.close()
    return port

def process(data):
    global BUSY, logger
    try:
        llm = ReflectModel()
        converter = OpenCC()
        data = converter.filter(data)
        for output_token in llm.complete(data):
            yield converter.filter(output_token)
    except Exception as e:
        logger.error(e)
    finally:
        BUSY = False
        logger.debug('Finished.')

async def api(request):
    global BUSY, logger
    if BUSY: return JSONResponse({'message': 'Processing another request'}, 503)
    
    async with request.form() as form:
        data = form.get('input')
        if data == None or data == '':
            logger.debug("I didn't see your input!")
            return JSONResponse({'message': "I didn't see your input!"}, 400)
        
        BUSY = True
        resp = EventSourceResponse(process(data))
        # resp.headers['Content-Type'] = 'text/event-stream; charset=utf-8'
        return resp

def register(retry_cnt, backoff_time=1):
    global CONFIG, logger
    logger.info('Attempting registration with the Agent... {} times left.'.format(retry_cnt))
    try:
        response = requests.post(
            urljoin(CONFIG['agent_endpoint'], '/register'),
            data={
                'name': CONFIG['LLM_name'],
                'endpoint':'http://{0}:{1}{2}'.format(CONFIG['public_ip'], CONFIG['port'], CONFIG['endpoint'])
                }
        )
        if response.text == 'Failed': raise Exception
        else:
            logger.info('Registered.')
            atexit.register(unregister)
    except Exception as e:
        logger.warning('The server failed to register to Agent. Cause: {}.'.format(str(e)))

        if retry_cnt == 0 and not CONFIG['ignore_agent']:
            logger.info('The program will exit now.')
            sys.exit(0)
        
        if retry_cnt != 0:
            logger.info('Will retry registration after {} seconds.'.format(backoff_time))
            # Exponential backoff
            time.sleep(backoff_time)
            register(retry_cnt-1, backoff_time*2)

def unregister():
    global CONFIG, logger
    logger.info('Attempting to unregister with the Agent...')
    try:
        response = requests.post(
            urljoin(CONFIG['agent_endpoint'], '/unregister'),
            data={
                'name': CONFIG['LLM_name'],
                'endpoint':'http://{0}:{1}{2}'.format(CONFIG['public_ip'], CONFIG['port'], CONFIG['endpoint'])
                }
        )
        if response.text == 'Failed':
            logger.warning('Failed to unregister from Agent. Refused by Agent.')
    except Exception as e:
        logger.warning('Failed to unregister from Agent. Cause: {}.'.format(str(e)))

def setup_logger():
    global CONFIG
    logging_format = '%(asctime)s [%(name)-5s] %(levelprefix)-4s %(message)s'
    logging_date_format = '%Y-%m-%d %H:%M:%S' 
    console_formatter = uvicorn.logging.ColourizedFormatter(
        fmt=logging_format, datefmt=logging_date_format,
        style="%", use_colors=True
    )

    loggers = [logging.getLogger(name) for name in logging.root.manager.loggerDict]
    for logger in [l for l in loggers if len(l.handlers) >= 1]:
        logger.setLevel(CONFIG['logging_level'])
        logger.handlers[0].setFormatter(console_formatter)
        logger.handlers[0].setLevel(CONFIG['logging_level'])

async def on_startup():
    setup_logger()
    register(CONFIG['retry_count'])

def override_config():
    global CONFIG

    # Override default configuration is the corresponding environment variable exists.
    CONFIG = {key: os.environ.get(key.upper(), default) for key, default in CONFIG.items()}
    CONFIG['port'] = CONFIG['port'] or assign_unused_port()
    CONFIG['ignore_agent'] = bool(CONFIG['ignore_agent'])
    CONFIG['retry_count'] = int(CONFIG['retry_count'])
    
    os.environ['CUDA_VISIBLE_DEVICES'] = '1'

if __name__ == '__main__':
    override_config()
    routes = [
        Route(CONFIG['endpoint'], endpoint=api, methods=['POST'])
    ]

    app = Starlette(debug=True, routes=routes, on_startup=[on_startup])
    uvicorn.run(app, host="0.0.0.0", port=CONFIG['port'], log_level=CONFIG['logging_level'])