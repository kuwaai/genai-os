## 架設模型教學

這個指南將幫助您在啟動Kernel後進行設定，Kernel預設會在`127.0.0.1:9000`上架設。如果你已經啟動Kernel，就可以開始在Kernel上架設您自己的模型。
可以連線到
http://127.0.0.1:9000/v1.0/worker/debug
來檢查Kernel目前的狀態。

### 基礎使用

**1. 安裝所需套件**

請確保您已安裝所需的套件，執行以下指令來安裝：

```sh
pip install -r requirements.txt
```

我們已將executor包成package，因此安裝後可以直接使用該指令`kuwa-executor`來啟動executor，請使用以下指令閱讀更多詳細的參數：
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
可以針對細部的executor類型取得更深入的說明，舉例如下
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

**2. 準備您的模型**

如果您的模型是 `.gguf` 格式，請使用：
```sh
kuwa-executor llamacpp --model_path <PATH_TO_YOUR_GGUF> --visible_gpu <CUDA_VISIBLE_DEVICES>
```
來架設模型。對於可由transformers載入的模型（`.safetensors`、`.bin`、`.model` 等），或存放於Huggingface上的模型，請使用：
```sh
kuwa-executor huggingface --model_path <PATH_TO_MODEL_FOLDER/HUGGINGFACE_MODEL_NAME> --visible_gpu <CUDA_VISIBLE_DEVICES>
```

**3. 串接雲端模型**

您也可以使用 API 金鑰連接到 Gemini 或 ChatGPT這類的雲端模型。

- 使用以下指令來啟動executor，api_key為全域的預設key，可不填。

  ```sh
  kuwa-executor geminipro --api_key <YOUR_API_KEY>
  kuwa-executor chatgpt --api_key <YOUR_API_KEY> --model <gpt-3.5-turbo/gpt-4/gpt-4-32k/...>
  ```

- 預設情況下，這兩個會使用 `geminipro` 和 `chatgpt` 作為access_code來架設模型。若要調整架設的access_code，請使用 `--access_code <您想要的訪問碼>`。

### 進階使用

#### 細部生成設定

除了除錯用的 Executor 外，其他 Executor 都可使用設定檔或是命令列參數指定生成設定的參數，地端模型還可以指定 System prompt 與 Prompt template，詳情請使用`kuwa-executor [executor] --help`查看。

#### 自訂 Executor

Kuwa Executor在架構上可以看作一個執行特殊函數、提供特定功能的伺服器，目前的介面定義在`kuwa.executor.LLMExecutor`，其行為是接受使用者的聊天歷史紀錄並輸出文字，您可以透過繼承以上類別，來自訂自己的Executor。

`LLMExecutor`最簡單的實做可以參考此目錄下的`debug.py`或是`dummy.py`，各個API的說明如下:
- `__init__`: 初始化服務，請務必呼叫`super().__init__()`以完成初始化
- `extend_arguments`: 可彈性新增命令列參數，使用內建函式庫`argparse`解析參數
- `setup`: 使用者自訂初始化內容。此階段命令列參數已解析完畢，可透過`self.args`變數存取
- `llm_compute`: 主要處裡請求的方法，請使用非同步迭代器 (Async iterator) 方式撰寫
- `abort`: 使用者中斷請求時呼叫的方法，預期應中斷目前正在執行的請求

#### 串接其他推論環境

Kuwa Executor 可以簡易串接其他推論環境，輕鬆與現有的開源軟體整合。  
目前只要是相容 OpenAI API 的推論伺服器都可以使用 ChatGPT Executor 串接。  
以下以 [vLLM](https://github.com/vllm-project/vllm) 為例，他是一個高吞吐量的推論引擎。

**1. 啟動 vLLM 伺服器** (使用 Google Gemma 7B Instruct 模型作為範例)
```sh
docker run --runtime nvidia --gpus all \
    -v ~/.cache/huggingface:/root/.cache/huggingface \
    --env "HUGGING_FACE_HUB_TOKEN=<secret>" \
    -p 8000:8000 \
    --ipc=host \
    vllm/vllm-openai:latest \
    --model google/gemma-7b-it --dtype half
```

**2. 啟動 Kuwa 的 ChatGPT Executor**
```sh
kuwa-executor chatgpt --access_code vllm --log debug \
    --base_url "http://localhost:8000/v1" `# 將 API 的基礎路徑改為 vLLM`\
    --api_key dummy `# API Key 隨意`\
    --no_override_api_key `#Disable override the system API key with user API key.` \
    --model "google/gemma-7b-it" `# 選擇 Gemma 7B 模型`
```

#### 串接 TAIDE API

國家高速網路與計算中心提供的 TAIDE API 也可以輕鬆透過我們的 ChatGPT executor 串接。
```sh
kuwa-executor chatgpt --access_code taide-api --log debug \
    --base_url "https://td.nchc.org.tw/api/v1/" `#國網中心的 TAIDE API 路徑` \
    --no_override_api_key `#Disable override the system API key with user API key.` \
    --api_key "YOUR_API_KEY" `#輸入已申請的 API key` \
    --model "TAIDE/a.2.0.0-SG" `#使用a.2.0.0模型` \
    --system_prompt "你是一個來自台灣的AI助理，你的名字是TAIDE，樂於以台灣人的立場幫助使用者，會用繁體中文回答問題。" `#TAIDE的預設system prompt`
```