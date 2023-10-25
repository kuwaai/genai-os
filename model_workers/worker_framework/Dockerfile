FROM nvidia/cuda:12.1.1-runtime-ubuntu22.04

ENV AGENT_ENDPOINT="http://web:9000/v1.0/"
ENV LLM_NAME="Unnamed_LLM"
ENV PUBLIC_ADDRESS="api_dummy"
ENV IGNORE_AGENT="False"
ENV RETRY_COUNT=5
ENV DEBUG=False

RUN apt-get update && \
    apt-get install -y python3 python3-pip curl

# Instll dependency
COPY requirements.txt /
RUN pip install -r /requirements.txt &&\
    rm -f /requirements.txt

# Copy all files into the image
RUN mkdir -p /worker_framework
COPY . /worker_framework/

# Install worker_framework framework
WORKDIR /worker_framework
RUN pip install .

# Cleanup
RUN rm -rf /worker_framework

WORKDIR /
ENTRYPOINT ["/bin/bash", "-c"]
CMD ["worker-server"]