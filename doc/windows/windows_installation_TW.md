## Windows可攜式安裝教學

我們提供了針對 Windows x64 的可攜式版本，預設使用 SQLite 作為資料庫，所需套件在下載解壓縮後占用大約1.5GB，請多加留意所花的網路流量。

請按照以下步驟進行建置：

### 前置條件
- 確保您已安裝 [VC_redist.exe](https://learn.microsoft.com/zh-tw/cpp/windows/latest-supported-vc-redist?view=msvc-170)。
- 如要在GPU上載入模型，請先安裝好[CUDA](https://developer.nvidia.com/cuda-toolkit)。

### 快速安裝
- 預設會啟用Gemini Pro與ChatGPT兩個模型，如需連帶執行gguf模型可提前先丟至executors\llamacpp路徑下。
- 過程會創建管理者權限帳號，如需重新創建，請參照[常見問題](#常見問題)第一項。
```bat
git clone https://github.com/kuwaai/genai-os.git
cd genai-os/windows
"build & start.bat"
```
- 關閉系統請輸入`stop`指令，直接關閉視窗可能會無法順利釋放記憶體，不小心關閉視窗請參照[常見問題](#常見問題)第三項。
- 後續啟動可直接跑`start.bat`，如有更新、移動專案路徑，請重跑`build.bat`或`build & start.bat`。

### 詳細安裝步驟

1. **從Release下載，或用git bash執行以下指令複製專案並切換至專案內的 windows 資料夾：**
   ```bat
   git clone https://github.com/kuwaai/genai-os.git
   cd genai-os/windows
   ```

2. **下載相關套件並快速進行設定：**
   ```bat
   .\build.bat
   ```

3. **啟動應用程式：**
   - 執行 `start.bat` 以啟動應用程式。注意：如果您有以下任何服務正在運行（nginx、php、php-cgi、python、redis-server），這個執行檔會在關閉時終止它們。還請確保端口 80、9000 和 6379 未被使用。
   ```bat
   .\start.bat
   ```
   - 此時應該會被要求創建管理者帳號(需輸入名稱、信箱、密碼)，如果沒有跳出來，或輸入錯誤、創建失敗，請見[此處](#常見問題)。

4. **檢查應用程式狀態：**
   - 成功的話會自動開啟您的瀏覽器到 `127.0.0.1`。如果有看到網頁介面，那安裝應該沒有問題。

5. **如何關閉程式：**
   - 請盡量不要強制關閉.bat檔案(包含用紅色叉叉直接關閉)，目前礙於.bat無法在這些情況下自動幫你關閉所有開啟的程式來釋放資源

   - **因此請在執行`start.bat`時養成習慣輸入`stop`的方式來關閉該程式。**

6. **設定模型：**
   - 剛啟動該程式的狀態下預設有ChatGPT、Gemini Pro，兩種模型皆為串接API，因此需要申請對應的API Key，如果您想啟動自己的模型，或串接其他的API，則需要設定executors，但這部分由於篇幅龐大，請參考[該處](./executors/README_TW.md)的教學指南。

## 常見問題

1. **Q: 沒有被要求創建管理者帳號、管理者帳號創建失敗、輸入錯誤...**
   
   A: 請打開`tool.bat`，然後輸入`seed`並Enter，就會打開管理員帳號創建的介面了，創建完成之後輸入`quit`即可關閉。

2. **Q: 我將整個專案移動位置後，執行start.bat卻一堆錯誤，網頁也404/500無法進入。**

   A: 由於該專案一些部份必須使用絕對路徑，因此如果抵達該專案目錄的路徑有變(改上層資料夾名稱或移動整個專案位置)，則需要重跑`build.bat`來將絕對路徑做更新，workers資料夾內的模型也是，建議重新執行`init.bat`以免出錯。

3. **Q: 不小心將整個start.bat的程式給直接按下紅色叉叉關閉了，背景程式沒有被關閉導致記憶體資源依舊被占用，我該怎麼辦？**

   A: 礙於.bat檔無法在您戳紅色叉叉的時候把所有程式都一同關閉，你可以打開`tool.bat`，並輸入`stop`來將所有相關程式都終止。

在安裝過程中遇到任何問題，請隨時聯絡我們。