### Model Deployment Tutorial

This guide will help you set up your model deployment after you've launched a kernel. By default, the kernel will be hosted at `127.0.0.1:9000`. If you have already launched the kernel, you can start deploying your own model on it. You can check the current status of your kernel by visiting
http://127.0.0.1:9000/v1.0/worker/debug.

**1. Install Required Packages**

Make sure you have the required packages installed by running:

```sh
pip install -r requirements.txt
```

We have packaged the executor as a package, so you can directly use the command `kuwa-executor` to start the executor after installation. You can read more detailed parameters by running the command:
```sh
> kuwa-executor --list

Available model workers:

debug      : [Tool] Debugging worker. It will reflect the last input.
dummy      : [Tool] Dummy worker. It will reply fixed message regardless of the user prompt.
geminipro  : [Cloud model] Google Gemini-Pro. Need API key.
chatgpt    : [Cloud model] OpenAI ChatGPT. Need API key.
huggingface: [On-premises model] Download and run Huggingface model locally.
llamacpp   : [On-premises model] Run the GGUF model locally.

Use "kuwa-executor [worker] --help" to get more information.
```
You can get more detailed help for specific worker types, for example:
```sh
> kuwa-executor debug --help

usage: kuwa-executor debug [-h] [--access_code ACCESS_CODE] [--version VERSION] [--ignore_kernel] [--https]
                           [--host HOST] [--port PORT] [--worker_path WORKER_PATH] [--kernel_url KERNEL_URL]
                           [--log {NOTSET,DEBUG,INFO,WARNING,ERROR,CRITICAL}] [--delay DELAY]

LLM model worker, Please make sure your kernel is working before use.

optional arguments:
  -h, --help            show this help message and exit
  --delay DELAY         Inter-token delay (default: 0.02)

General Options:
  --access_code ACCESS_CODE
                        Access code (default: None)
  --version VERSION     Version of the executor interface (default: v1.0)
  --ignore_kernel       Ignore kernel (default: False)
  --https               Register the worker endpoint with https scheme (default: False)
  --host HOST           The hostname or IP address that will be stored in Kernel, Make sure the location are
                        accessible by Kernel (default: None)
  --port PORT           The port to serve. By choosing None, it'll assign an unused port (default: None)
  --worker_path WORKER_PATH
                        The path this model worker is going to use (default: /chat)
  --kernel_url KERNEL_URL
                        Base URL of Kernel's executor management API (default: http://127.0.0.1:9000/)
  --log {NOTSET,DEBUG,INFO,WARNING,ERROR,CRITICAL}
                        The logging level. (default: INFO)
```

**2. Prepare Your Model**

To deploy a model if your model is in `.gguf` format, use:
```sh
kuwa-executor llamacpp --model_path <PATH_TO_YOUR_GGUF> --visible_gpu <CUDA_VISIBLE_DEVICES>
```
For models that can be loaded by transformers (`.safetensors`, `.bin`, `.model`, etc.) or models hosted on Huggingface:
```sh
kuwa-executor huggingface --model_path <PATH_TO_MODEL_FOLDER/PATH_TO_HUGGINGFACE> --visible_gpu <CUDA_VISIBLE_DEVICES>
```

**3. Connecting Cloud Models**

You can also connect to cloud models like Gemini Pro or ChatGPT using API keys.

- Start the worker with commands below, `api_key` is a global default key, so you can omit it.


  ```sh
  kuwa-executor geminipro --api_key <YOUR_API_KEY>
  kuwa-executor chatgpt --api_key <YOUR_API_KEY> --model <gpt-3.5-turbo/gpt-4/gpt-4-32k/...>
  ```

- By default, both of them will use `gemini-pro` and `chatgpt` as the `access_code` when deploying the model. If you want to adjust the `access_code` of the deployment, you can use: `--access_code <your desired access code>`.

**4. Advanced Usage**

You can inherit the `kuwa.LLMWorker` class to customize your own worker, but this is not ready to be demonstrated here, stay tuned...