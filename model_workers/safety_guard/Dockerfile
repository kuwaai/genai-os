ARG WORKER_FRAMEWORK_VERSION=latest
FROM worker-framework:${WORKER_FRAMEWORK_VERSION}

# Install python packages
COPY requirements.txt /model_api/
RUN pip install -r /model_api/requirements.txt

# Install the source code of application in the last stage.
# By doing so, we can utilize the building cache of Docker to speedup the building process.
RUN mkdir -p /model_api
COPY . /model_api/
WORKDIR /model_api/

RUN chmod +x run.sh

ENV LLM_NAME="safety-guard"
ENV PUBLIC_ADDRESS="localhost"
ENV PORT="9001"
ENV AGENT_ENDPOINT="http://localhost:9000/v1.0/"
ENV DEBUG="False"

ENTRYPOINT ["/bin/bash", "-c"]
CMD ["./run.sh"]