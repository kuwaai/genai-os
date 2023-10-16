Document QA (DocQA)
---
Retrieve-Augmented Generation Worker.

## Production Deployment
### Dependency

- Docker with nvidia runtime: To run the container
- [Squid](http://www.squid-cache.org/): For caching downloaded files and web pages
- ../model_framework: The common framework of workers

### Step

1. Setup the environment
    - Setup the environment variables
        ```bash
        cp .env.example .env.prod
        # Edit .env.prod
        # AGENT_ENDPOINT: The public base endpoint of the Agent.
        # PUBLIC_ADDRESS: The public address of the worker that can be accessed by the Agent.
        # IGNORE_AGENT: Set to "True" means start the worker even if registration failed.
        # DEBUG: Set to "True" to increase verbosity of the log.
        ```
    - Copy the LLM to the host directory `/var/models/`. This directory will be mounted into the container.  
      Currently, the model directory used is called `llama2-7b-chat-b5.0.0`.
        ```
        /var/models
        ├── llama2-7b-chat-b1.0.0
        ├── llama2-7b-chat-b2.0.0
        └── llama2-7b-chat-b5.0.0
            ├── config.json
            ├── generation_config.json
            ├── model-00001-of-00002.safetensors
            ├── model-00002-of-00002.safetensors
            ├── model.safetensors.index.json
            ├── special_tokens_map.json
            ├── tokenizer_config.json
            ├── tokenizer.json
            └── tokenizer.model
        ```

2. Run the Squid web cache server.  
   Please follow the instructions in the project root's `Docker/squid/README.md` file.  
   Note that for each host, there's only a need for one Squid service instance.

3. Build the base image of the model framework. The image will be tagged as `worker-framework:latest`

    ```bash
    pushd ../worker_framework
    ./build.sh
    popd # Back to this directory
    ```

4. Build and start a DocQA worker. For the following example, a worker container called `doc_qa` will expose at port `9002` and pin to the GPU`0`.
    ```bash
    sudo LLM_NAME="doc_qa" PORT="9002" GPU_ID="0" ./run_production.sh up -d --build --force-recreate
    ```

5. You can watch the log through the following command
    ```bash
    sudo LLM_NAME="doc_qa" PORT="9002" GPU_ID="0" ./run_production.sh logs -f
    ```
    The QA logs are kept in the volume `doc-qa-${PORT}_log` for further investigation.