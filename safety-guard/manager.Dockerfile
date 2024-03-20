FROM python:3.11-alpine

WORKDIR /usr/src/app

# curl for health check
RUN apk --no-cache add curl

COPY manager/requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY manager ./manager
COPY lib ./lib

WORKDIR /usr/src/app/manager

CMD [ "python", "./main.py" ]