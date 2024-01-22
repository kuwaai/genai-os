FROM python:3.11-alpine

WORKDIR /usr/src/app

COPY manager/requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY manager ./manager
COPY lib ./lib

WORKDIR /usr/src/app/manager

CMD [ "python", "./main.py" ]