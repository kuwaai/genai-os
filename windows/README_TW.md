## 可攜式安裝教學

我們提供了針對 Windows x64 的可攜式版本，使用 SQLite 作為資料庫。請按照以下步驟進行建置：

### 前置條件
- 確保您已安裝 [VC_redist.exe](https://learn.microsoft.com/zh-tw/cpp/windows/latest-supported-vc-redist?view=msvc-170)。

### 安裝步驟

1. **複製專案並切換至專案內的 windows 資料夾：**
   ```bat
   git clone https://github.com/kuwaai/genai-os.git
   cd genai-os/windows
   ```

2. **下載相依套件並設定：**
   ```bat
   .\build.bat
   ```

3. **啟動應用程式：**
   - 執行 `start.bat` 以啟動應用程式。注意：如果您有以下任何服務正在運行（nginx、php、php-cgi、python、redis-server），這個執行檔會在關閉時終止它們。還請確保端口 80、9000 和 6379 未被使用。
   ```bat
   .\start.bat
   ```

4. **檢查應用程式狀態：**
   - 成功的話會自動開啟您的瀏覽器到 `127.0.0.1`。如果有看到網頁介面，那安裝應該沒有問題。

5. **建立管理員帳號（如果還沒有建立任何帳號）：**
   - 執行 `seed.bat` 並按照提示建立管理員帳號（提供姓名、電子郵件和密碼）。您可以使用此帳號登入。

6. **設定模型並開始使用 Kuwa GenAI OS：**
   - 設定您的模型資訊並開始使用 Kuwa GenAI OS。

在安裝過程中遇到任何問題，請隨時聯絡我們。