FROM python:3.10-slim

WORKDIR /usr/src/app

RUN apt-get update &&\
    apt-get install -y cmake build-essential git

# Install the dependency of each executor
COPY docker/executor/requirements.txt ./requirements-docker.txt
RUN pip install --no-cache-dir -r requirements-docker.txt

COPY src/executor/requirements.txt ./
RUN sed -i '/^\.[\/]*/d' ./requirements.txt &&\
    sed -i '/torch.*/d' ./requirements.txt &&\
    pip install --no-cache-dir -r requirements.txt

# Dependency of llama-cpp-python == 0.2.87
# Ref: https://github.com/abetlen/llama-cpp-python/issues/1628
RUN apt-get install -y musl-dev && \
    ln -s /usr/lib/x86_64-linux-musl/libc.so /lib/libc.musl-x86_64.so.1

COPY src/executor/docqa/requirements.txt ./docqa/requirements.txt
RUN pip install --no-cache-dir -r ./docqa/requirements.txt

COPY src/executor/speech_recognition/requirements.txt ./speech_recognition/requirements.txt
RUN apt-get install -y ffmpeg &&\
    pip install --no-cache-dir -r ./speech_recognition/requirements.txt

COPY src/executor/image_generation/requirements.txt ./image_generation/requirements.txt
RUN pip install --no-cache-dir -r ./image_generation/requirements.txt

COPY src/executor/uploader/requirements.txt ./uploader/requirements.txt
RUN pip install --no-cache-dir -r ./uploader/requirements.txt

COPY src/tools/requirements.txt ./tools/requirements.txt
RUN pip install --no-cache-dir -r ./tools/requirements.txt

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
ENV KUWA_ROOT="/var/kuwa/docker/root"
VOLUME ${KUWA_ROOT}
RUN mkdir -p ${KUWA_ROOT}/bin && \
    mkdir -p ${KUWA_ROOT}/database && \
    mkdir -p ${KUWA_ROOT}/custom
COPY src/tools ${KUWA_ROOT}/../../src/tools

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