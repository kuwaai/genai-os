FROM model-api

RUN mkdir -p /model_api

RUN apt-get update &&\
    apt-get install -y libxml2-dev libxslt1-dev antiword unrtf poppler-utils pstotext tesseract-ocr \
            flac ffmpeg lame libmad0 libsox-fmt-mp3 sox libjpeg-dev swig

COPY requirements.txt /model_api/
RUN pip install -r /model_api/requirements.txt

COPY pre_cache_models.py /
RUN python3 /pre_cache_models.py

COPY . /model_api/
WORKDIR /model_api/

RUN chmod +x run.sh

ENV LLM_NAME="doc-qa"
ENV PUBLIC_ADDRESS="localhost"
ENV PORT="9001"
ENV AGENT_ENDPOINT="http://localhost:9000/v1.0/"
# ENV ENDPOINT="/"
ENV DEBUG="False"

ENTRYPOINT ["/bin/bash", "-c"]
CMD ["./run.sh"]