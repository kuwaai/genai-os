### Hosting Your Model Guide

This guide assists you in hosting your model after starting the agent, typically hosted on `127.0.0.1:9000` by default. Once connected, you can begin hosting your model on the agent.

**1. Install Required Packages**

Ensure you have installed the required packages by running:

```sh
pip install -r requirements1.txt
pip install -r requirements2.txt
```

**2. Prepare Your Model**

If your model is in a `.gguf` file, use `llamacpp.py` to host the model worker. For models from Hugging Face (`.safetensors`, `.bin`, `.model`, etc.), use `huggingface.py`.

**3. Hosting Models without Local Hardware**

If you don't have the hardware to host models, you can still connect to Gemini Pro or ChatGPT using the API token.

- Use `Gemini Pro.py` or `chatgpt.py` and provide your API key:

  ```sh
  python3 geminipro.py --api_key <YOUR_API_KEY>
  python3 chatgpt.py --api_key <YOUR_API_KEY>
  ```

- By default, these will host your model under the access code `gemini-pro` and `chatgpt`. To change this, use `--access_code <THE_ACCESS_CODE_YOU_WANT>`.

- For additional parameters, use `--help`:

  ```sh
  usage: geminipro.py [-h] [--access_code ACCESS_CODE] [--version VERSION] [--ignore_agent] [--public_ip PUBLIC_IP]
                    [--port PORT] [--worker_path WORKER_PATH] [--limit LIMIT] [--model_path MODEL_PATH]
                    [--prompt_path PROMPT_PATH] [--gpu_config GPU_CONFIG] [--agent_endpoint AGENT_ENDPOINT]
                    [--api_key API_KEY]
  ```
