FROM model-api

RUN mkdir -p /model_api

COPY requirements.txt /model_api/
RUN pip install -r /model_api/requirements.txt

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