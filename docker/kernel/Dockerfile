FROM python:3.10-alpine

WORKDIR /usr/src/app

# COPY src/kernel/requirements.txt ./
# RUN pip install --no-cache-dir -r requirements.txt

RUN apk update && apk add git
COPY src/kernel/. .
COPY .git ../../.git
RUN pip install --no-cache-dir -r requirements.txt &&\
    rm -rf ../../.git &&\
    apk del git

CMD [ "kuwa-kernel" ]