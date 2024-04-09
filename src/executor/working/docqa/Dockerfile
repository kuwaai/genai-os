ARG WORKER_FRAMEWORK_VERSION=latest
FROM worker-framework:${WORKER_FRAMEWORK_VERSION}

RUN mkdir -p /model_api

# Install the dependency.
# Note that this order can prevent re-runing apt-get if requirements.txt changed.
RUN apt-get update &&\
    apt-get install -y libxml2-dev libxslt1-dev antiword unrtf poppler-utils pstotext tesseract-ocr \
            flac ffmpeg lame libmad0 libsox-fmt-mp3 sox libjpeg-dev swig

# Install Google Chrome to fetch Client-Side Rendering page
# https://googlechromelabs.github.io/chrome-for-testing/
WORKDIR /opt/
ENV CHROME_VERSION=119.0.6045.105
RUN apt-get install -y curl unzip && \
    curl -OL "https://edgedl.me.gvt1.com/edgedl/chrome/chrome-for-testing/${CHROME_VERSION}/linux64/chrome-linux64.zip" &&\
    curl -OL "https://edgedl.me.gvt1.com/edgedl/chrome/chrome-for-testing/${CHROME_VERSION}/linux64/chromedriver-linux64.zip" &&\
    unzip chrome-linux64.zip && \
    unzip chromedriver-linux64.zip
ENV PATH="$PATH:/opt/chrome-linux64:/opt/chromedriver-linux64"
RUN apt-get install -y --no-install-recommends libatk1.0-0 libatk-bridge2.0-0 \
    libxkbcommon-x11-0 libgbm1 libpango-1.0-0 libxcomposite1 libxdamage1 && \
    chrome --version && chromedriver --version && \
    rm -f chrome-linux64.zip chromedriver-linux64.zip
WORKDIR /

# Install python packages
COPY requirements.txt /model_api/
RUN pip install -r /model_api/requirements.txt

# Pre-caching the used embedding model from huggingface hub.
COPY pre_cache_models.py /
RUN python3 /pre_cache_models.py

# Install the source code of application in the last stage.
# By doing so, we can utilize the building cache of Docker to speedup the building process.
COPY . /model_api/
WORKDIR /model_api/
RUN chmod +x run.sh

ENV LLM_NAME="doc-qa"
ENV PUBLIC_ADDRESS="localhost"
ENV PORT="9001"
ENV AGENT_ENDPOINT="http://localhost:9000/v1.0/"
ENV DEBUG="False"
# "local" or "remote-nchc"
ENV MODEL_LOCATION="remote-nchc"

ENTRYPOINT ["/bin/bash", "-c"]
CMD ["./run.sh"]