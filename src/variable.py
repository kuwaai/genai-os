import os
from datetime import datetime

version = "v1.0"
data = {}
log_folder = "logs"
record_file = "records.pickle"
safety_guard = "safety-guard"

port = 9000
ip = "0.0.0.0"

log_file_path = os.path.join(log_folder, datetime.now().strftime('%Y-%m-%d_%H-%M-%S.%f') + '.log')