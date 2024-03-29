### 架設模型教學

這個指南將幫助您在啟動Agent後進行設定，Agent預設會在`127.0.0.1:9000`上架設。如果你已經啟動Agent，就可以開始在Agent上架設您自己的模型。

**1. 安裝所需套件**

確保您已安裝所需的套件，方法如下：

```sh
pip install -r requirements1.txt
pip install -r requirements2.txt
```

**2. 準備您的模型**

如果您的模型是 `.gguf` 格式，請使用 `llamacpp.py` 來架設模型。對於從 Hugging Face 取得的模型（`.safetensors`、`.bin`、`.model` 等），請使用 `huggingface.py`。

**3. 串接雲端模型**

您也可以使用 API 金鑰連接到 Gemini Pro 或 ChatGPT這類的雲端模型。

- 使用 `Gemini Pro.py` 或 `chatgpt.py` 並提供您的 API 金鑰：

  ```sh
  python3 geminipro.py --api_key <YOUR_API_KEY>
  python3 chatgpt.py --api_key <YOUR_API_KEY>
  ```

- 預設情況下，這兩個會使用 `gemini-pro` 和 `chatgpt` 作為access_code來架設模型。若要調整架設的access_code，請使用 `--access_code <您想要的訪問碼>`。

- 如需更多參數，請使用 `--help`：

  ```sh
  usage: geminipro.py [-h] [--access_code ACCESS_CODE] [--version VERSION] [--ignore_agent] [--public_ip PUBLIC_IP]
                    [--port PORT] [--worker_path WORKER_PATH] [--limit LIMIT] [--model_path MODEL_PATH]
                    [--prompt_path PROMPT_PATH] [--gpu_config GPU_CONFIG] [--agent_endpoint AGENT_ENDPOINT]
                    [--api_key API_KEY]
  ```