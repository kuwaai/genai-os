import grpc
from concurrent.futures import ThreadPoolExecutor

# Type definition
from detection_pb2 import CheckingResponse, ChatRecord
# Service definition
from detection_pb2_grpc import DetectionServicer, add_DetectionServicer_to_server

class DetectionService(DetectionServicer):
    def PreFilter(self, request, context):
        assert request.chat_records[-1].role == ChatRecord.ROLE_USER
        print(request)
        return CheckingResponse(safe=True)

    def PostFilter(self, request, context):
        assert request.chat_records[-1].role == ChatRecord.ROLE_ASSISTANT
        print(request)
        return CheckingResponse(
            safe=False,
            action=CheckingResponse.ACTION_BLOCK,
            message="The request has been blocked."
        )

def serve():
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
    server = grpc.server(
        thread_pool=ThreadPoolExecutor(max_workers=10),
        options=server_options,
    )
    add_DetectionServicer_to_server(DetectionService(), server)
    server.add_insecure_port('[::]:50051')
    server.start()
    server.wait_for_termination()

if __name__ == '__main__':
    serve()