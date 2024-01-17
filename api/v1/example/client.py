import grpc
import json
import logging

# Type definition
from detection_pb2 import (
    FilterRequest,
    CheckingResponse,
    ChatRecord
)
# Client stub definition
from detection_pb2_grpc import DetectionStub

def print_checking_result(resp: CheckingResponse):
    print(f'Safe: {resp.safe}')
    if not resp.safe:
        print(f'Action: {resp.action}')
        if resp.message:
            print(f'Message: {resp.message}')

def run():

    # Enable the health checking top the server.
    service_config_json = json.dumps({"healthCheckConfig": {"serviceName": "Detection"}})
    options = [("grpc.service_config", service_config_json)]

    channel = grpc.insecure_channel('localhost:50051')
    
    stub = DetectionStub(channel)

    print("Calling PreFilter()")
    try:
        response = stub.PreFilter(
            FilterRequest(
                model_id = 'test-model',
                chat_records=[
                    ChatRecord(role=ChatRecord.ROLE_USER, content='ping')
                ]
            ),
        )
        print_checking_result(response)
    except Exception as e:
        logging.exception("Failed to make RPC.")

    # Or use the 'wait_for_ready' option to wait until the server is ready.
    print("Calling PostFilter()")
    response = stub.PostFilter(
        FilterRequest(
            model_id = 'test-model',
            chat_records=[
                ChatRecord(role=ChatRecord.ROLE_USER, content='ping'),
                ChatRecord(role=ChatRecord.ROLE_ASSISTANT, content='pong'),
            ]
        ),
        wait_for_ready=True
    )
    print_checking_result(response)

if __name__ == '__main__':
    logging.basicConfig(level=logging.INFO)
    run()