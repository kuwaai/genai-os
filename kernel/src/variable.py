import os
from datetime import datetime

version = "v1.0"
data = {}
log_folder = "logs"
record_file = "records.pickle"

port = 9000
ip = "0.0.0.0"

log_file_path = os.path.join(log_folder, datetime.now().strftime('%Y-%m-%d_%H-%M-%S.%f') + '.log')

# Set following environment variable before importing the Safety Guard client
os.environ['SAFETY_GUARD_MANAGER_URL'] = 'http://localhost:8000'
os.environ['SAFETY_GUARD_DETECTOR_URL'] = 'grpc://localhost:50051'
safety_guard_update_interval_sec = 30