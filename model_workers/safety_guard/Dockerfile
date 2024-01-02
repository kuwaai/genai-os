FROM worker-framework

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