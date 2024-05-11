## Model Serving Tutorial

This guide will help you setup your model serving after your Kernel is started. By default, the kernel should be served at `127.0.0.1:9000`. If you've already started your Kernel, you should be ready to host your own model on your kernel.

You can check the current serving status of the Kernel by connecting to
http://127.0.0.1:9000/v1.0/worker/debug

### Basic Usage

**1. Install Required Packages**
 
Make sure you've installed the required packages by running:
```sh
pip install -r requirements.txt
```
The executor is packaged so that after installation you can directly use the `kuwa-executor` command to start up your executor. You can get more detailed parameters with:
```sh
> kuwa-executor --list

Available model executors:

debug      : [Tool] Debugging executor. It will reflect the last input.
dummy      : [Tool] Dummy executor. It will reply fixed message regardless of the user prompt.
geminipro  : [Cloud model] Google Gemini-Pro. Need API key.
chatgpt    : [Cloud model] OpenAI ChatGPT. Need API key.
huggingface: [On-premises model] Download and run Huggingface model locally.
llamacpp   : [On-premises model] Run the GGUF model locally.

Use "kuwa-executor [executor] --help" to get more information.
```
You can get more detailed instructions for a specific executor type as well:
```sh
> kuwa-executor debug --help

usage: kuwa-executor debug [-h] [--access_code ACCESS_CODE] [--version VERSION] [--ignore_kernel] [--https]
                           [--host HOST] [--port PORT] [--executor_path EXECUTOR_PATH] [--kernel_url KERNEL_URL]
                           [--log {NOTSET,DEBUG,INFO,WARNING,ERROR,CRITICAL}] [--delay DELAY]

LLM model executor, Please make sure your kernel is working before use.

optional arguments:
  -h, --help            show this help message and exit
  --delay DELAY         Inter-token delay (default: 0.02)

General Options:
  --access_code ACCESS_CODE
                        Access code (default: None)
  --version VERSION     Version of the executor interface (default: v1.0)
  --ignore_kernel       Ignore kernel (default: False)
  --https               Register the executor endpoint with https scheme (default: False)
  --host HOST           The hostname or IP address that will be stored in Kernel, Make sure the location are
                        accessible by Kernel (default: None)
  --port PORT           The port to serve. By choosing None, it'll assign an unused port (default: None)
  --executor_path EXECUTOR_PATH
                        The path this model executor is going to use (default: /chat)
  --kernel_url KERNEL_URL
                        Base URL of Kernel's executor management API (default: http://127.0.0.1:9000/)
  --log {NOTSET,DEBUG,INFO,WARNING,ERROR,CRITICAL}
                        The logging level. (default: INFO)
```

**2. Prepare Your Model**

If your model is in `.gguf` format:
```sh
kuwa-executor llamacpp --model_path <PATH_TO_YOUR_GGUF> --visible_gpu <CUDA_VISIBLE_DEVICES>
```
For models loadable by transformers (`.safetensors`, `.bin`, `.model`, etc.) or hosted by Huggingface:
```sh
kuwa-executor huggingface --model_path <PATH_TO_MODEL_FOLDER/HUGGINGFACE_MODEL_NAME> --visible_gpu <CUDA_VISIBLE_DEVICES>
```

**3. Connect to Cloud Model**

You can use API Keys to connect to cloud models such as Gemini Pro or ChatGPT.

- Start the executor with the following commands. The `api_key` is optional and will default to the global value.

  ```sh
  kuwa-executor geminipro --api_key <YOUR_API_KEY>
  kuwa-executor chatgpt --api_key <YOUR_API_KEY> --model <gpt-3.5-turbo/gpt-4/gpt-4-32k/...>
  ```

- By default, these will set up the executor with `gemini-pro` and `chatgpt` as the `access_code` respectively. If you'd like to adjust the `access_code` the executor is setup with, you can use `--access_code <your_desired_access_code>`.

### Advanced Usage

#### Detailed Generation Args

In addition to the debug executor, other executors allow you to specify detailed generation args, either through config file or command line arguments. On-premises models also allow you to specify a system prompt and prompt template. For details, use `kuwa-executor [executor] --help`.

#### Custom Executors

Kuwa Executor can be viewed as a function or server that provides a specific functionality. The interface is defined in `kuwa.executor.LLMExecutor`, which is a function that takes in the user's chat history and outputs text. You can extend this class to define your own custom executor.

The simplest implementation of `LLMExecutor` can be seen in `debug.py` and `dummy.py`. Here is an explanation of each API:
- `__init__`: Initialize the service. Make sure to call `super().__init__()` to complete the initialization.
- `extend_arguments`: Optionally add command-line arguments. Use the `argparse` built-in library for parsing arguments.
- `setup`: Initialize anything you need. The command line arguments have been parsed at this stage and can be accessed via the `self.args` variable.
- `llm_compute`: The main method for handling requests. Please use an asynchronous iterator to implement this method.
- `abort`: Called when the request is aborted by the user. It is expected to interrupt the current request in progress.

#### Connecting to other Inference Environments

Kuwa Executor can be easily connected to other inference environments, making it easy to integrate with existing open-source software.

Currently, any OpenAI API compatible inference server can be used with the ChatGPT Executor.

Here's an example using [vLLM](https://github.com/vllm-project/vllm), a high-throughput inference engine.

**1. Start vLLM Server** (Shown using the Google Gemma 7B Instruct model as an example)
```sh
docker run --runtime nvidia --gpus all \
    -v ~/.cache/huggingface:/root/.cache/huggingface \
    --env "HUGGING_FACE_HUB_TOKEN=<secret>" \
    -p 8000:8000 \
    --ipc=host \
    vllm/vllm-openai:latest \
    --model google/gemma-7b-it --dtype half
```

**2. Start Kuwa ChatGPT Executor**
```sh
kuwa-executor chatgpt --access_code vllm --log debug \
    --base_url "http://localhost:8000/v1" `# Change the API base URL to vLLM` \
    --api_key dummy `# Dummy API Key` \
    --model "google/gemma-7b-it" `# Specify Gemma 7B model`
```

#### Integrating the TAIDE API

The TAIDE API provided by the National Center for High-performance Computing (NCHC) can also be easily integrated with our ChatGPT Executor.

```sh    
kuwa-executor chatgpt --access_code taide-api --log debug \
    --base_url "https://td.nchc.org.tw/api/v1/" `#TAIDE API path on NCHC` \
    --api_key "YOUR_API_KEY" `#Input the API key that you have applied for` \
    --model "TAIDE/a.2.0.0-SG" `#Using the a.2.0.0 model` \
    --system_prompt "你是一個來自台灣的AI助理，你的名字是TAIDE，樂於以台灣人的立場幫助使用者，會用繁體中文回答問題。" `#TAIDE's default system prompt`
```