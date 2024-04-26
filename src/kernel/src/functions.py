import os, logging, re, gzip, pickle, requests
from urllib.parse import urlparse
from flask import make_response
from json import dumps
from src.variable import *

logger = logging.getLogger(__name__)


# def init_folder(path):
#     if not os.path.exists(path):
#         os.mkdir(path)

# def log(state, *args, **kwargs):
#     log_level = ["INFO", "WARNING", "CRITIAL"]
#     message = ' '.join(map(str, args))
#     msg = f"[{datetime.now().strftime('%Y-%m-%d %H-%M-%S.%f')}][{log_level[state]}] {message}"
#     print(msg)
#     with open(log_file_path, "a") as file:
#         file.write(msg + "\n")

def save_variable_to_file(filename, data):
    with gzip.open(filename, 'wb') as file:
        pickle.dump(data, file, protocol=pickle.HIGHEST_PROTOCOL)
    logger.info(f"Records saved\n{data}")

def load_variable_from_file(filename):
    with gzip.open(filename, 'rb') as file:
        return pickle.load(file)

def endpoint_formatter(endpoint):
    return endpoint[:-1] if endpoint.endswith("/") else endpoint
        
        
def get_base_url(url):
    parsed_url = urlparse(url)
    base_url = f"{parsed_url.scheme}://{parsed_url.netloc}"
    return base_url
    
def load_records(var, keep_state = False):
    logger.info(f"Loading records, Here's before\n{data}")
    logger.info(f"Here's new records\n{var}")
    for i,o in var.items():
        data[i] = []
        for k in o:
            if not(endpoint_formatter(k[0]) in [j[0] for j in data.get(i, [])]):
                try:
                    resp = requests.get(get_base_url(k[0]) + "/health", timeout=20)
                    if resp.status_code == 204:
                        data[i].append(k if keep_state else [endpoint_formatter(k[0]),"READY",-1,-1])
                    else:
                        log(0,f"Healthy check failed in {i} at {k[0]}, removed")
                except requests.exceptions.ConnectionError as e:
                    log(0,f"Healthy check failed in {i} at {k[0]}, removed")
        if len(data[i]) == 0: del data[i]
    logger.info(f"Records loaded, Here's After\n{data}")