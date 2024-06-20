FROM python:3.11-bookworm
#alpine have issue when installing torch

WORKDIR /usr/src/app

# gRPC health probe
RUN GRPC_HEALTH_PROBE_VERSION=v0.4.13 && \
    curl -L -o /bin/grpc_health_probe https://github.com/grpc-ecosystem/grpc-health-probe/releases/download/${GRPC_HEALTH_PROBE_VERSION}/grpc_health_probe-linux-amd64 && \
    chmod +x /bin/grpc_health_probe

COPY detector/requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY detector ./detector
COPY lib ./lib

WORKDIR /usr/src/app/detector

CMD [ "python", "./main.py" ]