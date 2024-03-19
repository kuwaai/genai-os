# -#- coding: UTF-8 -*-
import time, re, os, logging, click, requests
from datetime import datetime
from src.variable import *
from src.functions import *

if __name__ == '__main__':
    # Setup logfile location
    init_folder(log_folder)
    
    
    # Setup console logger and file logger
    log_formatter = ANSIEscapeCodeRemovalHandler('[%(levelname)s] %(message)s')
    console_handler = logging.StreamHandler()
    file_handler = logging.FileHandler(log_file_path)
    file_handler.setLevel(logging.DEBUG)
    file_handler.setFormatter(log_formatter)
    logger = logging.getLogger()
    logger.addHandler(file_handler)
    console_handler.setFormatter(log_formatter)
    console_handler.setLevel(logging.DEBUG)
    logger.addHandler(console_handler)
    from src.safety_middleware import update_safety_guard
    from flask import Flask
    from flask_sse import ServerSentEventsBlueprint
    from apscheduler.schedulers.background import BackgroundScheduler
    from routes.worker import worker
    from routes.chat import chat
    
    # Load savefile
    if os.path.exists(record_file):
        loadRecords(load_variable_from_file(record_file))

    # Schedule background job to update the Safety Guard
    scheduler = BackgroundScheduler()
    scheduler.add_job(
        func=update_safety_guard,
        trigger="interval",
        seconds=safety_guard_update_interval_sec,
        next_run_time=datetime.now()
    )
    scheduler.start()

    # Init Flask Apps
    app = Flask(__name__)
    app.config["REDIS_URL"] = "redis://localhost:6379/0"
    sse = ServerSentEventsBlueprint('sse', __name__)
    app.register_blueprint(sse, url_prefix='/')
    app.register_blueprint(worker, url_prefix=f'/{version}/worker')
    app.register_blueprint(chat, url_prefix=f'/{version}/chat')
    log(0,"Route list:\n","\n".join([str(i) for i in app.url_map.iter_rules()]), "\n")
    log(0,"Server started")
    app.run(port=port, host=ip, threaded=True)
    #Stopped, saving to file
    save_variable_to_file(record_file, data)