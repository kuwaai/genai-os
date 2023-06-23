cd /API
apt update
apt install -y curl
while ! curl -s http://web:9000/debug >/dev/null; do
  echo "Waiting for connection to http://web:9000/debug ..."
  sleep 1
done

echo "Connected to http://web:9000/debug"
python3 LLaMA_TW1.py
