FROM python:3.10-slim

WORKDIR /usr/src/app

RUN apt-get update &&\
    apt-get install -y cmake build-essential

# Install the dependency of executor
COPY src/executor/requirements.txt ./
RUN sed -i '/\.[/]*/d' ./requirements.txt &&\
    sed -i '/torch.*/d' ./requirements.txt
RUN pip install --no-cache-dir -r requirements.txt

# Install the executor framework
COPY src/executor/. .
RUN pip install .

# Install the multi-chat-client and the entrypoint
COPY docker/executor/multi-chat-client/requirements.txt /tmp/
RUN pip install --no-cache-dir -r /tmp/requirements.txt
COPY docker/executor/multi-chat-client/multi-chat-client /usr/local/bin/
RUN chmod +x /usr/local/bin/multi-chat-client
COPY docker/executor/docker-entrypoint /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint

# Default parameters
ENV EXECUTOR_TYPE="debug"
ENV EXECUTOR_ACCESS_CODE="debug"
ENV EXECUTOR_NAME="Debug Executor"
ENV ADD_EXECUTOR_TO_MULTI_CHAT="true"
ENV KERNEL_URL="http://kernel:9000/"
ENTRYPOINT [ "docker-entrypoint" ]
CMD []