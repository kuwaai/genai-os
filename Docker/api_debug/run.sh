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
apt update
apt install -y curl
while ! curl -s http://web:9000/v1.0/worker/debug >/dev/null; do
  echo "Waiting for connection to http://web:9000/v1.0/worker/debug ..."
  sleep 1
done

echo "Connected to http://web:9000/v1.0/worker/debug"
export CUDA_VISIBLE_DEVICES=0 
python3 dummy.py &
PYTHON_PID=$!

wait $PYTHON_PID