#!/bin/bash

PORT=${PORT:-$(
        python -c 'import socket; s=socket.socket(); s.bind(("", 0)); print(s.getsockname()[1]); s.close()'
    )}
echo PORT=${PORT}
docker compose -p doc-qa-${PORT} -f docker-compose-production.yml "$@"