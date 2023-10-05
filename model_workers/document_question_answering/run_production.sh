#!/bin/bash

docker compose -p doc-qa-${PORT} -f docker-compose-production.yml "$@"