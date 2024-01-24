Safety Guard API Specification
===
Version: `1.0.0`

## Detection API
- Style: gRPC
- Client component: Agent

### Service
- `MessageFilter`: Perform the safety check on a single message.
- `QaFilter`: Perform the safety check on a prompt-response pair.

### Usage
1. Build the Stub
```bash
pip install grpc-io grpc-tools
python -m grpc_tools.protoc --python_out=./example --grpc_python_out=./example -I. detection.proto
python -m grpc_tools.protoc --python_out=../../lib/grpc --grpc_python_out=../../lib/grpc -I. detection.proto
# You don't have to understand the code in the generated files
# "detection_pb2_grpc.py" and "detection_pb2.py". All you need is copy these
# generated files to the directory of your client code.
```
2. For the sample client/server code, please refer to the `example/` directory.

## Management API
- Style: Web API
- Client component: Demo site web server

### Endpoints
- Keyword create, read, and delete
- Embedding create, read, and delete
- For the OpenAPI specification, please refer to `management.yaml`