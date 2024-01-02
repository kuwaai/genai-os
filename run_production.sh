#!/bin/bash

PORT=${PORT:-$(
        python -c 'import socket; s=socket.socket(); s.bind(("", 0)); print(s.getsockname()[1]); s.close()'
    )}

GPU_ID=${GPU_ID:-0}
COMPOSE_FILE=${COMPOSE_FILE:-compose.yaml}

echo PORT=${PORT} GPU_ID=${GPU_ID}
docker compose -p safety-guard-${PORT} -f ${COMPOSE_FILE} "$@"