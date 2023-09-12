FROM nvidia/cuda:12.1.1-runtime-ubuntu22.04

ENV AGENT_ENDPOINT="http://web:9000/"
ENV LLM_NAME="Unnamed_LLM"
ENV PUBLIC_ADDRESS="model-api"
ENV ENDPOINT="/v1/completion"
ENV IGNORE_AGENT="False"
ENV RETRY_COUNT=5

# Set working directory
WORKDIR /

# Copy any setup files into the image
COPY requirements.txt /requirements.txt

# Run setup commands
RUN apt-get update && \
    apt-get install -y python3 python3-pip curl && \
    pip install -r requirements.txt &&\
    rm -f requirements.txt

RUN mkdir -p /API
COPY src/* /API/

WORKDIR /API

CMD ["/bin/python3", "main.py"]
