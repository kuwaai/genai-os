FROM nvidia/cuda:12.1.1-runtime-ubuntu22.04

ENV AGENT_ENDPOINT="http://web:9000/v1.0/"
ENV LLM_NAME="Unnamed_LLM"
ENV PUBLIC_ADDRESS="model-api"
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
RUN mkdir -p /model_api_server
COPY . /model_api_server/

# Install model_api_server framework
WORKDIR /model_api_server
RUN pip install .

# Cleanup
RUN rm -rf /model_api_server

WORKDIR /
ENTRYPOINT ["/bin/bash", "-c"]
CMD ["model-api-server"]