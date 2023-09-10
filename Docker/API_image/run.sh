#!/bin/bash
set -e
function cleanup() {
    echo "Stopping Python program..."
    kill -SIGTERM $PYTHON_PID
    wait $PYTHON_PID
    echo "Python program stopped."
    exit
}
trap cleanup SIGTERM

cd /API
python3 model_api.py &
PYTHON_PID=$!

wait $PYTHON_PID