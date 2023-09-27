FROM model-api

RUN mkdir -p /model_api

COPY . /model_api/
WORKDIR /model_api/

RUN pip install -r requirements.txt

RUN chmod +x run.sh

ENV LLM_NAME="chinese-convert"
ENV PUBLIC_ADDRESS="localhost"
ENV PORT="9001"
ENV AGENT_ENDPOINT="http://localhost:9000/v1.0/"
# ENV ENDPOINT="/"

# ENTRYPOINT ["/bin/bash", "-c"]
CMD ["./run.sh"]