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

As the framework operates asynchronously, it's essential to ensure that any blocking function, whether it's I/O-blocking or CPU-blocking, is invoked within an executor to facilitate concurrency [1] [3]. The official Python documentation presents three options for achieving concurrency within the asyncio framework [2], as illustrated in the code snippets below.

In many scenarios, executing a blocking function within the default event loop's executor or within a tailored thread pool suffices.
However, when dealing with a CPU-intensive function, it's advisable to run it in a separate process to prevent it from stalling the event loop.
It's also important to note that using the `multiprocessing` library within the asyncio framework may terminate the HTTP server unexpectedly since the signal file descriptor is shared between the parent and child process [4].

```python
def blocking_fn(x):
    # Blocking operations.
    # E.g. file I/O, downloading files, loading models, inferencing.
    # ...
    return x

def cpu_bound(x):
    # CPU bound operations.
    # E.g. batch splitting, mapping with CPU.
    # ...
    return x

async def main():
    """
    The entry point of your filter/model/layout.
    """

    loop = asyncio.get_running_loop()

    ## Options:

    # 1. Run in the default loop's executor:
    result = await loop.run_in_executor(None, blocking_fn)
    print('default thread pool', result)

    # 2. Run in a custom thread pool:
    with concurrent.futures.ThreadPoolExecutor() as pool:
        result = await loop.run_in_executor(pool, blocking_fn)
        print('custom thread pool', result)

    # 3. Run in a custom process pool:
    with concurrent.futures.ProcessPoolExecutor() as pool:
        result = await loop.run_in_executor(pool, cpu_bound)
        print('custom process pool', result)
```

References:  
[1] [_Python documentation_, "Developing with asyncio - Running Blocking Code"](https://docs.python.org/3/library/asyncio-dev.html#running-blocking-code)  
[2] [_Python documentation_, "Event Loop - Executing Code in Thread or Process Pools"](https://docs.python.org/3/library/asyncio-eventloop.html#executing-code-in-thread-or-process-pools)  
[3] [A.A. Masnun, "Async Python: The Different Forms of Concurrency"](http://masnun.rocks/2016/10/06/async-python-the-different-forms-of-concurrency/)  
[4] [FastAPI gets terminated when child multiprocessing process terminated #1487](https://github.com/tiangolo/fastapi/issues/1487#issuecomment-1157066306)  


### Interfaces of Default Layout
#### LLM

#### Text-level Filter

### Metrics