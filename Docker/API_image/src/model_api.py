#!/bin/python3
# -#- coding: UTF-8 -*-

import time, requests, sys, socket, os, logging, atexit
from flask import Flask, request, Response, abort, make_response, jsonify
from flask_sse import ServerSentEventsBlueprint
from completion import CompletionInterface

app = Flask(__name__)
app.config["REDIS_URL"] = os.environ.get("REDIS_URL", "redis://redis:6379/0")
app.register_blueprint(ServerSentEventsBlueprint('sse', __name__), url_prefix='/')

# The default value of configuration.
# The value can be override by specifying environment variables.
CONFIG = {
    'agent_endpoint': 'http://web:9000/', # The root endpoint of the Agent.
    'LLM_name': 'Unnamed LLM', # The name of this model.
    'public_ip': 'model-api', # The address that can be accessed by the Agent.
    'port': None, # The public port number for this API. Leave it as None to have it assigned by the system.
    'ignore_agent': False, # Continue running regardless of whether register successfully with the Agent.
    'logging_level': logging.INFO, 
}

# Global state to indicate whether the model is processing another request.
BUSY = False

def assign_unused_port():
    sock = socket.socket()
    sock.bind(('', 0))
    port = sock.getsockname()[1]
    sock.close()
    return port

def process(data):
    global BUSY
    try:
        yield data
    except Exception as e:
        logging.error(e)
    finally:
        BUSY = False
        logging.debug('Finished.')

@app.route('/', methods=['POST'])
def api():
    global BUSY
    if BUSY: abort(make_response(jsonify(message='Processing another request'), 503))
    
    data = request.form.get('input')
    if data == None or data == '':
        logging.debug("I didn't see your input!")
        abort(make_response(jsonify(message="I didn't see your input!"), 400))
        return
    
    BUSY = True
    resp = Response(process(data), mimetype='text/event-stream')
    resp.headers['Content-Type'] = 'text/event-stream; charset=utf-8'
    return resp

def register():
    global CONFIG
    logging.info('Attempting registration with the Agent...')
    try:
        response = requests.post(
            CONFIG['agent_endpoint'] + 'register',
            data={'name': CONFIG['LLM_name'],'endpoint':'http://{0}:{1}/'.format(CONFIG['public_ip'], CONFIG['port'])}
        )
        if response.text == 'Failed': raise Exception
        else:
            logging.info('Registered.')
            atexit.register(unregister)
    except Exception as e:
        reason = 'Connection error.' if isinstance(e, requests.exceptions.ConnectionError) else 'Refused by Agent.'
        logging.warning('The server failed to register to Agent. {}'.format(reason))

        if not CONFIG['ignore_agent']:
            logging.info('The program will exit now.')
            sys.exit(0)

def unregister():
    global CONFIG
    logging.info('Attempting to unregister with the Agent...')
    try:
        response = requests.post(
            CONFIG['agent_endpoint'] + 'unregister',
            data={'name': CONFIG['LLM_name'],'endpoint':'http://{0}:{1}/'.format(CONFIG['public_ip'], CONFIG['port'])}
        )
        if response.text == 'Failed':
            logging.warning('Failed to unregister from Agent. Refused by Agent.')
    except requests.exceptions.ConnectionError as e:
        logging.warning('Failed to unregister from Agent. Connection error.')

def configuration():
    global CONFIG
    CONFIG = {key: os.environ.get(key.upper(), default) for key, default in CONFIG.items()}
    CONFIG['port'] = CONFIG['port'] or assign_unused_port()
    CONFIG['ignore_agent'] = bool(CONFIG['ignore_agent'])
    
    os.environ['CUDA_VISIBLE_DEVICES'] = '1'
    logging.basicConfig(
        format="[%(asctime)s][%(name)-5s][%(levelname)-5s] %(message)s (%(filename)s:%(lineno)d)",
        datefmt="%Y-%m-%d %H:%M:%S",
        level = CONFIG['logging_level']
    )

if __name__ == '__main__':
    configuration()
    register()
    app.run(port=CONFIG['port'], host='0.0.0.0')