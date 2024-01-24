import os,sys
import grpc
import logging
from typing import List, Dict, Any

sys.path.append(os.path.join(os.path.dirname(__file__), '../../lib'))
sys.path.append(os.path.join(os.path.dirname(__file__), '../../lib/grpc'))

# Type definition
from detection_pb2 import CheckingResponse, ChatRecord
# Service definition
from detection_pb2_grpc import DetectionServicer, add_DetectionServicer_to_server

from model.detector import ChainEnum
from model.rule import ActionEnum
from .local_storage import get_guard_storage

logger = logging.getLogger(__name__)

# Coroutines to be invoked when the event loop is shutting down.
_cleanup_coroutines = []

class DetectionService(DetectionServicer):
    """
    Serving the incoming detection requests.
    1. Get the corresponding MixedGuard with (model_id, chain)
    2. Check the request with the MixedGuard pipeline
    3. Response (action, message) based on the checking result
    """

    async def PreFilter(self, request, context):
        return await self._filter(request, context, chain=ChainEnum.pre_filter)
    
    async def PostFilter(self, request, context):
        return await self._filter(request, context, chain=ChainEnum.post_filter)

    async def _filter(self, request, context, chain:ChainEnum):
        last_role_map = {
            ChainEnum.pre_filter: ChatRecord.ROLE_USER,
            ChainEnum.post_filter: ChatRecord.ROLE_ASSISTANT
            }
        assert request.chat_records[-1].role == last_role_map[chain]
        print(request)
        chat_records = DetectionService.convert_chat_records(request.chat_records)
        store = get_guard_storage()
        target_rules = store.get(request.model_id, chain)
        checking_result = await target_rules.guard.check(chat_records)
        return DetectionService.gen_checking_response(checking_result, target_rules.actions)

    @staticmethod
    def convert_chat_records(records: List[ChatRecord]) -> [Dict[str, str]]:
        role_map = {
            ChatRecord.ROLE_UNSPECIFIED: None,
            ChatRecord.ROLE_USER: 'user',
            ChatRecord.ROLE_ASSISTANT: 'bot'
        }
        result = [
            {'role': role_map[i.role], 'msg': i.content}
            for i in records
        ]
        return result
    
    @staticmethod
    def gen_checking_response(
            checking_result: Dict[int, Dict[str, Any]],
            actions: Dict[int, Dict[str, Any]]
        ) -> CheckingResponse:
        logger.debug(f'Checking result: {checking_result}')
        logger.debug(f'Actions: {actions}')
        safe = True
        action = ActionEnum.none
        message = ''
        for k, v in checking_result.items():
            if not v['violate']: continue
            safe = False
            action = actions[k]['action']
            if action == ActionEnum.overwrite:
                message = v.get('violate')
            else:
                message = actions[k]['message']
        action = {
            ActionEnum.none: None,
            ActionEnum.warn: CheckingResponse.ACTION_WARN,
            ActionEnum.block: CheckingResponse.ACTION_BLOCK,
            ActionEnum.overwrite: CheckingResponse.ACTION_OVERWRITE,
        }[action]
        return CheckingResponse(safe=safe, action=action, message=message)


async def serve():
    server_options = [
        ("grpc.keepalive_time_ms", 20000),
        ("grpc.keepalive_timeout_ms", 10000),
        ("grpc.http2.min_ping_interval_without_data_ms", 5000),
        ("grpc.max_connection_idle_ms", 10000),
        ("grpc.max_connection_age_ms", 30000),
        ("grpc.max_connection_age_grace_ms", 5000),
        ("grpc.http2.max_pings_without_data", 5),
        ("grpc.keepalive_permit_without_calls", 1),
    ]
    server = grpc.aio.server(
        options=server_options,
    )
    add_DetectionServicer_to_server(DetectionService(), server)
    listen_addr = "[::]:50051"
    server.add_insecure_port(listen_addr)
    logging.info("Starting server on %s", listen_addr)
    await server.start()

    async def server_graceful_shutdown():
        logging.info("Starting graceful shutdown...")
        # Shuts down the server with 5 seconds of grace period. During the
        # grace period, the server won't accept new connections and allow
        # existing RPCs to continue within the grace period.
        await server.stop(5)

    _cleanup_coroutines.append(server_graceful_shutdown())
    await server.wait_for_termination()