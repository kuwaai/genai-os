Safety Guard Client
===

This is the client library of the safety guard system. The library is expected
to be integrated into the proxy server on the primary prompt/response path.

## Usage
1. Set the following environment variable before importing the Safety Guard client library
```shell
# variable=default
SAFETY_GUARD_MANAGER_URL=http://localhost:8000
SAFETY_GUARD_DETECTOR_URL=grpc://localhost:50051
```