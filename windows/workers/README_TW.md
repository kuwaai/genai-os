## Windows可攜式模型架設教學
目前為了將模型架設簡單化，在Windows可攜版準備了一套簡易的模型管理系統，該管理系統僅為簡易使用與測試，並不建議用於Production的情況，如果是希望用於Production，請瀏覽該[教學](../../src/executor/README_TW.md)。

這邊的模型架設教學假設您已經執行過`windows/build.bat`與`windows/start.bat`，且系統可登入沒有問題，如果以上不符合您的情況，請先回到[該篇教學](../README_TW.md)的步驟。

## 介紹
在`windows/workers`資料夾中，您應該會看到以下資料夾，您可以任意更改資料夾名稱，不過以下五個名稱為預設保留，方便您快速設定模型：
1. chatgpt
2. custom
3. geminipro
4. huggingface
5. llamacpp

每個資料夾下都有一個init.bat，用於設定env.bat檔案。您可以直接編輯env.bat檔案，或自行編寫檔案，但請確保參數和格式無誤，且設定完成需重啟start.bat才會生效。

也可以將圖片放置於該資料夾內，讓其在初次建立模型的時候自動放入圖片(如果網站上已創建該模型，則需手動放入圖片)

預設這些模型都只會開放給擁有管理Tab權限的使用者。

## 模型快速設定教學

### ChatGPT
1. 進入`chatgpt`資料夾。
2. 執行`init.bat`檔案。
3. 輸入OpenAI API Token。若不想設定全域Token，可留空白直接按Enter跳過。

### Gemini Pro
1. 進入`geminipro`資料夾。
2. 執行`init.bat`檔案。
3. 輸入Google API Token。若不想設定全域Token，可留空白直接按Enter跳過。

### LLaMA.cpp
1. 進入`llamacpp`資料夾。
2. 放置.gguf檔案在該資料夾下
3. 執行`init.bat`檔案。
4. 若找不到.gguf檔案，請輸入該檔案的絕對路徑。

### Huggingface
1. 進入`huggingface`資料夾。
2. 放置模型與tokenizer。
3. 執行`init.bat`檔案，將自動偵測該目錄是否有模型。若未自動偵測到，請輸入模型資料夾的絕對路徑或在huggingface上的模型位置。

### Custom
- 此處為預留的自訂模型，使用者可以自行改寫一版executor(繼承kuwa LLMWorker)，在此指定.py檔案來方便執行，步驟與上面都一樣，只是多了worker_path這個參數，需要用絕對路徑指向到該.py檔案。

## 進階用法
你可以使用相同的 `access_code` 在多個資料夾中執行多個模型。這樣做可以同時處理多個請求。你也可以複製資料夾以建立更多的模型執行。這些模型不一定要在同一台主機上，你可以將它們分散在多台主機上，只需要將 Kernel endpoint 設定到 kernel 上即可，詳細的教學請見[此處](../../src/executor/README_TW.md)。

如果你正在使用 ollama 或 vLLM 等其他 TGI 框架，你也可以使用 ChatGPT 的 worker 快速串接。