FROM worker-framework

RUN mkdir -p /model_api

COPY . /model_api/
WORKDIR /model_api/

RUN apt-get update && apt-get install -y opencc
RUN pip install -r requirements.txt
RUN bash ./install_dict.sh 

RUN chmod +x run.sh

ENV LLM_NAME="chinese-convert"
ENV PUBLIC_ADDRESS="localhost"
ENV PORT="9001"
ENV AGENT_ENDPOINT="http://localhost:9000/v1.0/"
ENV DEBUG="False"

ENTRYPOINT ["/bin/bash", "-c"]
CMD ["./run.sh"]