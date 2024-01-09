import grpc
from concurrent.futures import ThreadPoolExecutor

# Type definition
from detection_pb2 import CheckingResponse
# Service definition
from detection_pb2_grpc import DetectionServicer, add_DetectionServicer_to_server

class DetectionService(DetectionServicer):
    def MessageFilter(self, request, context):
        print(request)
        return CheckingResponse(safe=True)

    def QaFilter(self, request, context):
        print(request)
        return CheckingResponse(safe=True, detail="Test detail.")

def serve():
    server = grpc.server(ThreadPoolExecutor(max_workers=10))
    add_DetectionServicer_to_server(DetectionService(), server)
    server.add_insecure_port('[::]:50051')
    server.start()
    server.wait_for_termination()

if __name__ == '__main__':
    serve()