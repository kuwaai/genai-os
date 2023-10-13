#!/bin/bash

# Containers are identified by (LLM_NAME, PORT) tuple
LLM_NAME=${LLM_NAME:-doc_qa}
PORT=${PORT:-$(
        python -c 'import socket; s=socket.socket(); s.bind(("", 0)); print(s.getsockname()[1]); s.close()'
    )}

GPU_ID=${GPU_ID:-0}

echo PUBIC_PORT=${PUBLIC_PORT} GPU_ID=${GPU_ID}
docker compose -p doc-qa-${PORT} -f docker-compose-production.yml "$@"