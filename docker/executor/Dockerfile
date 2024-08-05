FROM python:3.10-slim

WORKDIR /usr/src/app

RUN apt-get update &&\
    apt-get install -y cmake build-essential git

# Install the dependency of executor
COPY docker/executor/requirements.txt ./requirements-docker.txt
RUN pip install --no-cache-dir -r requirements-docker.txt
COPY src/executor/requirements.txt ./
RUN sed -i '/^\.[\/]*/d' ./requirements.txt &&\
    sed -i '/torch.*/d' ./requirements.txt
RUN pip install --no-cache-dir -r requirements.txt
COPY src/executor/docqa/requirements.txt ./docqa/requirements.txt
RUN pip install --no-cache-dir -r ./docqa/requirements.txt
COPY src/executor/speech_recognition/requirements.txt ./speech_recognition/requirements.txt
RUN apt-get install -y ffmpeg &&\
    pip install --no-cache-dir -r ./speech_recognition/requirements.txt
COPY src/executor/image_generation/requirements.txt ./image_generation/requirements.txt
RUN pip install --no-cache-dir -r ./image_generation/requirements.txt

# Install the executor framework and client library
COPY .git ../../.git
COPY src/executor/. .
COPY src/library/client ../library/client

RUN pip install . &&\
    pip install --no-cache-dir ../library/client &&\
    rm -rf ../../.git

# Install the multi-chat-client and the entrypoint
COPY docker/executor/multi-chat-client/requirements.txt /tmp/
RUN pip install --no-cache-dir -r /tmp/requirements.txt
COPY docker/executor/multi-chat-client/multi-chat-client /usr/local/bin/
RUN chmod +x /usr/local/bin/multi-chat-client
COPY docker/executor/docker-entrypoint /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint

# Make the filesystem hierarchy of kuwa
RUN mkdir -p /var/kuwa/docker/root/bin && \
    mkdir -p /var/kuwa/docker/root/database && \
    mkdir -p /var/kuwa/docker/root/custom
RUN ln -s $(which python) /bin/python
COPY src/tools /var/kuwa/src/tools

# Default parameters
ENV no_proxy="kernel,web,localhost,127.0.0.0/8,::1,${no_proxy}"
ENV NO_PROXY="kernel,web,localhost,127.0.0.0/8,::1,${NO_PROXY}"
ENV EXECUTOR_TYPE="debug"
ENV EXECUTOR_ACCESS_CODE="debug"
ENV EXECUTOR_NAME="Debug Executor"
ENV EXECUTOR_IMAGE=""
ENV ADD_EXECUTOR_TO_MULTI_CHAT="true"
ENV KERNEL_URL="http://kernel:9000/"
ENTRYPOINT [ "docker-entrypoint" ]
CMD []