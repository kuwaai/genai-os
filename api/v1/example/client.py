import grpc
import json
import logging

# Type definition
from detection_pb2 import (
    MessageFilterRequest,
    QaFilterRequest,
    CheckingResponse
)
# Client stub definition
from detection_pb2_grpc import DetectionStub

def print_checking_result(resp: CheckingResponse):
    print(f'Safe: {resp.safe}')
    if resp.detail:
        print(f'Detail: {resp.detail}')

def run():

    # Enable the health checking top the server.
    service_config_json = json.dumps({"healthCheckConfig": {"serviceName": "Detection"}})
    options = [("grpc.service_config", service_config_json)]

    channel = grpc.insecure_channel('localhost:50051')
    
    stub = DetectionStub(channel)

    print("Calling MessageFilter()")
    try:
        response = stub.MessageFilter(
            MessageFilterRequest(message="Test message filter."),
        )
        print_checking_result(response)
    except Exception as e:
        logging.exception("Failed to make RPC.")

    # Or use the 'wait_for_ready' option to wait until the server is ready.
    print("Calling QaFilter()")
    response = stub.QaFilter(
        QaFilterRequest(prompt="Test prompt.", response="Test response."),
        wait_for_ready=True
    )
    print_checking_result(response)

if __name__ == '__main__':
    logging.basicConfig(level=logging.INFO)
    run()