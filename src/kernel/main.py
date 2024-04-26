# -#- coding: UTF-8 -*-
import time, re, os, click, requests, sys
import logging.config
import argparse
sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from datetime import datetime
from src.variable import *
from src.functions import load_records, load_variable_from_file, save_variable_to_file
from src.logger import KernelLoggerFactory
from src.safety_middleware import update_safety_guard
from flask import Flask
from flask_sse import ServerSentEventsBlueprint
from apscheduler.schedulers.background import BackgroundScheduler
from routes.executor import executor
from routes.chat import chat

logger = logging.getLogger(__name__)

if __name__ == '__main__':
    # Setup logfile location
    # init_folder(log_folder)
    
    # Setup console logger and file logger
    # log_formatter = ANSIEscapeCodeRemovalHandler('[%(levelname)s] %(message)s')
    # console_handler = logging.StreamHandler()
    # file_handler = logging.FileHandler(log_file_path)
    # file_handler.setLevel(logging.DEBUG)
    # file_handler.setFormatter(log_formatter)
    # logger = logging.getLogger()
    # logger.addHandler(file_handler)
    # console_handler.setFormatter(log_formatter)
    # console_handler.setLevel(logging.DEBUG)
    # logger.addHandler(console_handler)
    parser = argparse.ArgumentParser(prog='Kuwa Kernel', description='Kuwa Kernel')
    parser.add_argument('--log_level', type=str, default="INFO", help="Log level")
    args = parser.parse_args()
    logging.config.dictConfig(KernelLoggerFactory(level=args.log_level).get_config())
    
    # Load savefile
    if os.path.exists(record_file):
        load_records(load_variable_from_file(record_file))

    # Schedule background job to update the Safety Guard
    logging.getLogger('apscheduler.executors.default').setLevel(logging.WARNING)
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
    app.register_blueprint(executor, url_prefix=f'/{version}/worker')
    app.register_blueprint(chat, url_prefix=f'/{version}/chat')
    logger.info("Route list:\n{}\n".format('\n'.join([str(i) for i in app.url_map.iter_rules()])))
    logger.info("Server started")
    app.run(port=port, host=ip, threaded=True)
    #Stopped, saving to file
    save_variable_to_file(record_file, data)