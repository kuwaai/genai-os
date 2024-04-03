### 架設模型教學

這個指南將幫助您在啟動Kernel後進行設定，Kernel預設會在`127.0.0.1:9000`上架設。如果你已經啟動Kernel，就可以開始在Kernel上架設您自己的模型。
可以連線到
http://127.0.0.1:9000/v1.0/worker/debug
來檢查Kernel目前的狀態。

**1. 安裝所需套件**

請確保您已安裝所需的套件，執行以下指令來安裝：

```sh
pip install -r requirements.txt
```

我們已將executor包成package，因此安裝後可以直接使用該指令`kuwa-executor`來啟動executor，請使用以下指令閱讀更多詳細的參數：
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
可以針對細部的worker類型取得更深入的說明，舉例如下
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

**2. 準備您的模型**

如果您的模型是 `.gguf` 格式，請使用：
```sh
kuwa-executor llamacpp --model_path <PATH_TO_YOUR_GGUF> --visible_gpu <CUDA_VISIBLE_DEVICES>
```
來架設模型。對於可由transformers載入的模型（`.safetensors`、`.bin`、`.model` 等），或存放於Huggingface上的模型，請使用：
```sh
kuwa-executor huggingface --model_path <PATH_TO_MODEL_FOLDER/PATH_TO_HUGGINGFACE> --visible_gpu <CUDA_VISIBLE_DEVICES>
```

**3. 串接雲端模型**

您也可以使用 API 金鑰連接到 Gemini Pro 或 ChatGPT這類的雲端模型。

- 使用以下指令來啟動worker，api_key為全域的預設key，可不填。

  ```sh
  kuwa-executor geminipro --api_key <YOUR_API_KEY>
  kuwa-executor chatgpt --api_key <YOUR_API_KEY> --model <gpt-3.5-turbo/gpt-4/gpt-4-32k/...>
  ```

- 預設情況下，這兩個會使用 `gemini-pro` 和 `chatgpt` 作為access_code來架設模型。若要調整架設的access_code，請使用 `--access_code <您想要的訪問碼>`。

**4. 進階用法**

您可以繼承kuwa.LLMWorker物件，來自訂自己的Worker，但此處教學尚未準備好，待新增...