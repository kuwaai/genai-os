[English README.md](./README.md)

<h1 align="center">
  <br>
  <a href="https://kuwaai.tw/zh-Hant/">
  <img src="./src/multi-chat/public/images/kuwa.png" alt="Kuwa GenAI OS" width="200"></a>
  <br>
  Kuwa GenAI OS
  <br>
</h1>

<h4 align="center">一個自由、開放、安全且注重隱私的生成式人工智慧服務系統。</h4>

<p align="center">
  <a href="http://makeapullrequest.com">
    <img src="https://img.shields.io/badge/PRs-welcome-brightgreen.svg">
  </a>
  <a href="#">
    <img src="https://img.shields.io/badge/all_contributors-2-orange.svg?style=flat-square">
  </a>
  <a href="https://laravel.com/docs/10.x/releases">
    <img src="https://img.shields.io/badge/maintained%20with-Laravel-cc00ff.svg">
  </a>
</p>

<p align="center">
  <a href="#關鍵功能">關鍵功能</a> •
  <a href="#架構">架構</a> •
  <a href="#安裝指南">安裝指南</a> •
  <a href="#社區">社區</a> •
  <a href="#致謝">致謝</a> •
  <a href="#授權條款">授權條款</a>
</p>

## 關鍵功能

* 提供多語言GenAI開發與部署的整體解決方案，支援Windows及Linux

* 提供群聊、引用、完整 Prompt 列表的匯入/匯出/分享等友善使用功能

* 可靈活組合 Prompt x RAGs x Bot x 模型 x 硬體/GPUs以滿足應用所需

* 支援從虛擬主機、筆記型電腦、個人電腦、地端伺服器到公私雲端的各種環境

* 開放原始碼，允許開發人員貢獻並根據自己的需求打造自己的客製系統

![screenshot](./src/multi-chat/public/images/demo.gif)

## 架構
> **警告**: 本草案為初步版本，可能會有進一步的更改。

[![screenshot](./src/multi-chat/public/images/architecture.svg)](https://kuwaai.tw/os/Intro)

## 依賴套件

為了執行此應用，請確保您的系統上安裝了以下套件：

- Node.js v20.11.1 & npm
- PHP 8.1 & php-fpm & Composer
- Python 3.10 & pip
- Nginx 或 Apache
- Redis 6.0.20
- CUDA
- Git

請按照以下步驟在 Windows 和 Linux 上設置和執行：

## 安裝指南
如果你想嘗鮮試著快速跑起來玩玩，我們有提供[Windows可攜式版本](./windows/README_TW.md)與[Docker版本](./docker/README_TW.md)，分別在Windows 10 x64 與 Ubuntu 22.04LTS 環境測試過，可以試試看！

或者你可以參考以下步驟來將整套系統安裝在主機上，在繼續之前，請確保您已經安裝了上述所有依賴套件。
1. **複製專案:**
   ```sh
   git clone https://github.com/kuwaai/genai-os.git
   cd genai-os/src/multi-chat/
   ```

2. **安裝依賴套件:**

   - 對於 Linux:
     ```sh
     cp .env.dev .env
     cd executables/sh
     ./production_update.sh
     cd ../../../kernel
     pip install -r requirement.txt
     cd ../executor
     pip install -r requirement.txt
     sudo chown -R $(whoami):www-data /var/www/html
     ```

   - 對於 Windows:
     ```bat
     copy .env.dev .env
     cd executable/bat
     ./production_update.bat
     cd ../../../kernel
     pip install -r requirement.txt
     cd ../executor
     pip install -r requirement.txt
     ```

3. **設定 PHP 和 PHP-FPM:**
   - 確保已安裝並正確設定了 PHP。
   - 設定您的 Web 伺服器（Nginx 或 Apache），將 `src/multi-chat/public` 設置為網站根目錄。
   - 範例設置文件: [nginx_config_example](src/multi-chat/nginx_config_example), [php.ini](src/multi-chat/php.ini)
   - 推薦設置:
     - 為了RAG應用，PHP 最大上傳文件大小設置為至少 20MB。

4. **設定 Redis:**
   - 確保已安裝並執行 Redis 伺服器。
   - 可以從 `.env` 中調整相關設定。
   - 在 `src/multi-chat/` 下執行 `php artisan queue:work --timeout=0` 來啟動 Redis Worker，來處理使用者的請求，建議同時執行至少 5 個Redis Worker。

5. **執行應用程式:**
   - 啟動您的 Web 伺服器和 PHP-FPM。
   - 執行Kernel `src/kernel/main.py`。建議在執行之前將該Kernel資料夾複製到另一個位置。

6. **連線到應用程式:**
   - 首先您需要建立一個管理員帳號，前往 `src/multi-chat/`，並執行 `php artisan db:seed --class=AdminSeeder --force` 以播種您的第一個管理員帳號。
   - 打開您的瀏覽器，並連到你架設的Nginx/Apache應用程式的 URL。
   - 使用您的管理員帳號登錄，開始使用Kuwa GenAI OS

7. **架設模型:**
    - 預設是沒有模型的，請閱讀[這份README](./src/executor/README_TW.md)來架設一些模型。
    - 架設完畢後，模型不會屏空出現在網站上，管理員必須在網站上設定對應的access_code才能存取該模型。
    - 請注意架設模型前Kernel必須先啟動(你可以檢查`127.0.0.1:9000`是否可以連線來確定)

## 下載

您可以[下載](https://github.com/kuwaai/genai-os/releases)最新版本的Kuwa GenAI OS，支持Windows和Linux。

## 社區

[Discord](https://discord.gg/4HxYAkvdu5) - Kuwa AI Discord 社區伺服器

[Facebook](https://www.facebook.com/groups/g.kuwaai.org) - Kuwa AI 社區

[Facebook](https://www.facebook.com/groups/g.kuwaai.tw) - Kuwa AI 臺灣社群

[Google Group](https://groups.google.com/g/kuwa-dev) - kuwa-dev

## 公告

[Facebook](https://www.facebook.com/kuwaai) - Kuwa AI

[Google Group](https://groups.google.com/g/kuwa-announce) - kuwa-announce

## 支援

我們團隊目前只有兩個人，如果您對我們合力開發的這個專案感興趣，可以一起協助我們開發，幫助我們把這個開源專案做的更好，如果您願意協助，請不要猶豫，隨時與我們聯繫！

## 套件與程式

該專案用到了以下套件和程式：

- [PHP & PHP-FPM](https://www.php.net/)
- [Laravel 10](https://laravel.com/)
- [Python 3](https://www.python.org/)
- [Node.js](https://nodejs.org/)
- [Docker](https://www.docker.com/)
- [Redis](https://redis.io/)
- [Marked](https://github.com/chjj/marked)
- [highlight.js](https://highlightjs.org/)
- [NVIDIA CUDA](https://developer.nvidia.com/cuda-toolkit)

## 致謝
在此感謝國科會TAIDE計畫、台灣人工智慧學校對本計畫初期開發時的協助。
<a href="https://www.nuk.edu.tw/"><img src="./src/multi-chat/public/images/logo_NUK.jpg" height="100px"></a>
<a href="https://taide.tw/"><img src="./src/multi-chat/public/images/logo_taide.jpg" height="100px"></a>
<a href="https://www.nstc.gov.tw/"><img src="./src/multi-chat/public/images/logo_NSTCpng.jpg" height="100px"></a>
<a href="https://www.narlabs.org.tw/"><img src="./src/multi-chat/public/images/logo_NARlabs.jpg" height="100px"></a>
<a href="https://aiacademy.tw/"><img src="./src/multi-chat/public/images/logo_AIA.jpg" height="100px"></a>

## 授權條款
[MIT](./LICENSE)
