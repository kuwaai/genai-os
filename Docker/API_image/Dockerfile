FROM nvidia/cuda:12.1.1-runtime-ubuntu22.04

ENV AGENT_ENDPOINT="http://web:9000/"
ENV LLM_NAME="Unnamed_LLM"
ENV PUBLIC_IP="model-api"
ENV IGNORE_AGENT="False"

# Set working directory
WORKDIR /

# Copy any setup files or scripts into the image
COPY requirements.txt /requirements.txt
COPY run.sh /run.sh

# Run setup commands
RUN apt update && \
    apt install -y python3 python3-pip curl && \
    pip install -r requirements.txt &&\
    rm -f requirements.txt

RUN mkdir -p /API
COPY src/* /API/

RUN chmod +x /run.sh

CMD ["/bin/bash", "/run.sh"]
