# -#- coding: UTF-8 -*-
import time, re
from src.variable import *
from gevent import pywsgi
from flask import Flask
from flask_sse import ServerSentEventsBlueprint
from routes.worker import worker
from routes.chat import chat

app = Flask(__name__)
app.config["REDIS_URL"] = "redis://localhost:6379/0"
sse = ServerSentEventsBlueprint('sse', __name__)
app.register_blueprint(sse, url_prefix='/')
app.register_blueprint(worker, url_prefix=f'/{version}/worker')
app.register_blueprint(chat, url_prefix=f'/{version}/chat')
print("Route list:\n","\n".join([str(i) for i in app.url_map.iter_rules()]))
app = pywsgi.WSGIServer(("0.0.0.0",9000), app, spawn=10)
print("\n\nServer started")
app.serve_forever()