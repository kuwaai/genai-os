import os, sys
import logging
import json
import grpc
from enum import Enum
from timeit import default_timer
from typing import Optional
from urllib.parse import urlparse

# [TODO] Health check
# from grpc_health.v1.health_pb2 import (
#     HealthCheckRequest,
#     HealthCheckResponse
# )
# from grpc_health.v1.health_pb2_grpc import HealthStub

sys.path.append(os.path.join(os.path.dirname(__file__), '../../lib'))
# Type definition
from detection_pb2 import (
    FilterRequest,
    CheckingResponse,
    ChatRecord
)
# Client stub definition
from detection_pb2_grpc import DetectionStub

logger = logging.getLogger(__name__)

class ActionEnum(str, Enum):
    none = 'none'
    warn = 'warn'
    block = 'block'
    overwrite = 'overwrite'

class DetectionClient:
    """
    The client for the Detection API.
    """
    def __init__(self, health_check_period_sec=30):
        server_url = os.environ.get('SAFETY_GUARD_DETECTOR_URL', 'grpc://localhost:50051')
        server_url = urlparse(server_url)
        # Currently, we only support the grpc scheme.
        assert server_url.scheme == 'grpc'

        # Enable the health checking top the server.
        service_config_json = json.dumps({"healthCheckConfig": {"serviceName": "Detection"}})
        options = [("grpc.service_config", service_config_json)]
        self.channel = grpc.insecure_channel(server_url.netloc)
        self.detection_stub = DetectionStub(channel)
        # self.health_stub = HealthStub(channel)

        # self.health_check_period_sec = health_check_period_sec
        # self.last_health_check_time = -1
        self.health = True
        # self.health_check()
    
    def pre_filter(self, chat_history:[dict], model_id:str) -> (bool, ActionEnum, str|None):
        try:
            chat_records = self.parse_chat_history(chat_history) 
            response = self.detection_stub.PreFilter(
                FilterRequest(
                    model_id = model_id,
                    chat_records=chat_records
                ),
            )
            return self.parse_checking_result(response)
        except Exception as e:
            logging.exception("Failed to make pre-filter RPC.")
            return (True, ActionEnum.none, None)
    
    def post_filter(self, chat_history:[dict], response:str, model_id:str) -> (bool, ActionEnum, str|None):
        try:
            chat_records = self.parse_chat_history(chat_history, response) 
            response = self.detection_stub.PostFilter(
                FilterRequest(
                    model_id = model_id,
                    chat_records=chat_records
                ),
            )
            return self.parse_checking_result(response)
        except Exception as e:
            logging.exception("Failed to make post-filter RPC.")
            return (True, ActionEnum.none, None)

    def is_online(self) -> bool:
        """
        Check connection health
        """
        # if (default_timer()-self.last_health_check_time) > self.health_check_period_sec:
            # self.health_check()
        # return self.health

    # def health_check(self):
    #     self.last_health_check_time = default_timer()
    #     try:
    #         request = HealthCheckRequest(service="Detection")
    #         resp = self.health_stub.Check(request)
    #         if resp.status == HealthCheckResponse.SERVING:
    #             self.health = True
    #         elif resp.status == HealthCheckResponse.NOT_SERVING:
    #             raise RuntimeWarning('Health check to safety guard failed.')
    #     except Exception:
    #         self.health = False
    #         logging.warning("Detector stopped serving")
    #     print('Healthy', self.health)
    
    def parse_chat_history(self, chat_history:[dict], response:Optional[str]=None):
        role_map = {
            'user': ChatRecord.ROLE_USER,
            'assistant': ChatRecord.ROLE_ASSISTANT
        }
        result = [
            ChatRecord(role=role_map[r['role']], content=r['content'])
            for r in chat_history
        ]
        if response:
            result.append(ChatRecord(role=role_map['assistant'], content=response))
        return result
    
    def parse_checking_result(self, resp: CheckingResponse):
        action = {
            CheckingResponse.ACTION_UNSPECIFIED: ActionEnum.none,
            CheckingResponse.ACTION_WARN: ActionEnum.warn,
            CheckingResponse.ACTION_BLOCK: ActionEnum.block,
            CheckingResponse.ACTION_OVERWRITE: ActionEnum.overwrite
        }[resp.action]
        return (resp.safe, action, resp.message)