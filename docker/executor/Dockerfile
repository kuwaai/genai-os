FROM python:3.10-slim

WORKDIR /usr/src/app

RUN apt-get update &&\
    apt-get install -y cmake build-essential

COPY src/executor/requirements.txt ./
RUN sed -i '/\.[/]*/d' ./requirements.txt &&\
    sed -i '/torch.*/d' ./requirements.txt
RUN pip install --no-cache-dir -r requirements.txt

COPY src/executor/. .
RUN pip install .

CMD [ "kuwa-executor", "debug", "--kernel_url", "http://kernel:9000/" ]