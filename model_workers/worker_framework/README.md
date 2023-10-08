# Worker Framework
A framework to serve and compose LLMs.

## Feature
- Utilize asynchronous I/O to efficiently use CPU
- Flexible model layout which can be configured to simple pipeline or complex logic
- Automatic register to the Agent at start-up and gracefully unregister when shutdown
- Exported metrics that can be monitored by Prometheus

## Installation

The standalone environment is easier to set up and you can use it in the development stage.  
However, we recommend using the container environment to isolate each worker in the production stage.
### Standalone Environment

1. Create virtual environment (recommend)
    ```bash
    python -m venv .venv
    source .venv/bin/activate
    ```

2. Install dependency
    ```bash
    pip install -r requirements.txt
    ```

3. Install the worker framework
    ```bash
    pip install .

    # or, you can specify the "editable" option to synchronize the local package
    # with this directory

    pip install --editable .
    ```

4. Run the example
    - You need to run the Agent first
    - The filter of this example will convert Simplified Chinese to Traditional Chinese using [OpenCC](https://github.com/BYVoid/OpenCC)
    - Moreover, the model is a simple reflect model, i.e. the model will output what the user input
    - The developer can easily extend this example. For more details, please refer to the "Development" section
    ```bash
    cd example
    ./run.sh
    ```

### Container Environment

1. Build the base image
    ```bash
    docker build -t worker-framework .
    ```

2. Build the image of example worker
    
    ```bash
    docker build -t worker-example example
    ```

3. Run te example
    - You need to run the Agent first
    - The Agent service is assumed in the network `agent_backend` with hostname `agent` and exposed with port number `9000`

    ```bash
    docker run --name api_example \
        --network "agent_backend" \
        -e PUBLIC_ADDRESS="api_example" \
        -e AGENT_ENDPOINT="http://agent:9000/v1.0/" \
        worker-example
    ```

4. Access the worker
    - The worker should registered with the Agent now
    - You can access the worker through the API of te agent

## Development

### Architecture

### Layout

### Best-Practice

### Interfaces of Default Layout
#### LLM

#### Text-level Filter

### Metrics