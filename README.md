[正體中文 README_TW.md](./README_TW.md)

<h1 align="center">
  <br>
  <a href="https://kuwaai.tw/en-US/">
  <img src="./src/multi-chat/public/images/kuwa.png" alt="Kuwa GenAI OS" width="200"></a>
  <br>
  Kuwa GenAI OS
  <br>
</h1>

<h4 align="center">An open, free, secure, and privacy-focused Generative-AI Orchestrating System.</h4>

<p align="center">
  <a href="http://makeapullrequest.com">
    <img src="https://img.shields.io/badge/PRs-welcome-brightgreen.svg?logo=github&logoColor=white">
  </a>
  <a href="#">
    <img src="https://img.shields.io/badge/all_contributors-2-orange.svg?style=flat-square">
  </a>
  <a href="https://laravel.com/docs/10.x/releases">
    <img src="https://img.shields.io/badge/maintained%20with-Laravel-cc00ff.svg?logo=laravel&logoColor=white">
  </a>
  <a href="https://discord.gg/Gu7zPavEmN">
    <img src="https://img.shields.io/badge/discord-active-blue?logo=discord&logoColor=white">
  </a>
  <a href="#">
    <img src="https://img.shields.io/github/v/release/kuwaai/genai-os">
  </a>
  <a href="#">
    <img src="https://img.shields.io/github/downloads/kuwaai/genai-os/total">
  </a>
  <a href="#">
    <img src="https://img.shields.io/github/license/kuwaai/genai-os">
  </a>
  <a href="#">
    <img src="https://img.shields.io/github/stars/kuwaai">
  </a>
</p>

<p align="center">
  <a href="#key-features">Key Features</a> •
  <a href="#architecture">Architecture</a> •
  <a href="#installation-guide">Installation Guide</a> •
  <a href="#community">Community</a> •
  <a href="#acknowledgements">Acknowledgements</a> •
  <a href="#license">License</a>
</p>

## Key Features

* Multi-lingual turnkey solution for GenAI development and deployment on Linux and Windows

* Concurrent multi-chat, quoting, full prompt-list import/export/share, and more for users

* Flexible orchestration of prompts x RAGs x bots x models x hardware/GPUs

* Heterogeneous support from virtual hosts, laptops, PCs, and edge servers to cloud

* Open-sourced, allowing developers to contribute and customize the system according to their needs

![screenshot](./src/multi-chat/public/images/demo.gif)

## Architecture
> **Warning**: This a preliminary draft and may be subject to further changes.

[![screenshot](./src/multi-chat/public/images/architecture.svg)](https://kuwaai.tw/os/Intro)

## Dependencies

To run this application, please make sure the following packages are installed on your system:

- Node.js v20.11.1 & npm
- PHP 8.1 & php-fpm & Composer
- Python 3.10 & pip
- Nginx or Apache
- Redis 6.0.20
- CUDA
- Git

For Windows and Linux, please follow the steps below to set up and execute:

## Installation Guide
If you wish to test out a quick demo version, we provide a [Portable Windows version](./windows/README.md) and a [Docker version](./docker/README.md), which have been tested in Windows 10 x64 and Ubuntu 22.04LTS environments.

Alternatively, you can refer to the following steps to install the entire system on your host. Before proceeding, please ensure you have installed all the dependencies listed above.
1. **Clone the project:**
   ```sh
   git clone https://github.com/kuwaai/genai-os.git
   cd genai-os/src/multi-chat/
   ```

2. **Install dependencies:**

   - For Linux:
     ```sh
     cp .env.dev .env
     cd executable/sh
     ./production_update.sh
     cd ../../../kernel
     pip install -r requirement.txt
     cd ../executor
     pip install -r requirement.txt
     sudo chown -R $(whoami):www-data /var/www/html
     ```

   - For Windows:
     ```bat
     copy .env.dev .env
     cd executable/bat
     ./production_update.bat
     cd ../../../kernel
     pip install -r requirement.txt
     cd ../executor
     pip install -r requirement.txt
     ```

3. **Set up PHP & PHP-FPM:**
   - Make sure PHP is installed and configured properly.
   - Configure your web server (Nginx or Apache) to set `src/multi-chat/public` as the website root directory.
   - Example config files: [nginx_config_example](src/multi-chat/nginx_config_example), [php.ini](src/multi-chat/php.ini)
   - Recommended settings:
     - Set max upload file size in PHP to at least 20MB, for RAG applications.

4. **Set up Redis:**
   - Make sure you have a Redis server installed and running.
   - Relevant settings can be adjusted in `.env`.
   - Run `php artisan queue:work --timeout=0` under `src/multi-chat/` to start the Redis Worker, which handles user requests. We recommended running at least 5 Redis Workers at the same time.

5. **Run the application:**
   - Start your web server and PHP-FPM.
   - Run the Kernel `src/kernel/main.py`. It is recommended that you copy the Kernel folder to another location before running it.

6. **Connect to the application:**
   - First, you need to create an admin account. Go to `src/multi-chat/`, and run `php artisan db:seed --class=AdminSeeder --force` to seed your first admin account.
   - Open your browser and access the URL of your deployed Nginx/Apache application.
   - Log in with your admin account, and start using Kuwa GenAI OS!

7. **Deploy models:**
    - No model is provided by default. Please read [this README](./src/executor/README.md) to deploy some models.
    - The models do not appear automatically after deployment. The administrator must set the corresponding access_code on the website to access the model.
    - Please note that the Kernel must be started before deploying the model (you can check if you can connect to `127.0.0.1:9000` to confirm)

## Download

You can [download](https://github.com/kuwaai/genai-os/releases) the latest Kuwa GenAI OS version that supports Windows and Linux.

## Community

[Discord](https://discord.gg/4HxYAkvdu5) - Kuwa AI Discord community server

[Facebook](https://www.facebook.com/groups/g.kuwaai.org) - Kuwa AI Community

[Facebook](https://www.facebook.com/groups/g.kuwaai.tw) - Kuwa AI Taiwan community

[Google Group](https://groups.google.com/g/kuwa-dev) - kuwa-dev

## Announcement

[Facebook](https://www.facebook.com/kuwaai) - Kuwa AI

[Google Group](https://groups.google.com/g/kuwa-announce) - kuwa-announce

## Support

Our team currently has only two people. If you are interested in this project, you can help us develop it together and improve this open-source project. Please do not hesitate to contact us anytime if you are willing to help!

## Packages & Applications

The following packages and applications are used in this project:

- [PHP & PHP-FPM](https://www.php.net/)
- [Laravel 10](https://laravel.com/)
- [Python 3](https://www.python.org/)
- [Node.js](https://nodejs.org/)
- [Docker](https://www.docker.com/)
- [Redis](https://redis.io/)
- [Marked](https://github.com/chjj/marked)
- [highlight.js](https://highlightjs.org/)
- [NVIDIA CUDA](https://developer.nvidia.com/cuda-toolkit)

## Acknowledgements
We want to acknowledge NSTC's TAIDE project and the Taiwan AI Academy for their assistance in the early development of this project.
<a href="https://www.nuk.edu.tw/"><img src="./src/multi-chat/public/images/logo_NUK.jpg" height="100px"></a>
<a href="https://taide.tw/"><img src="./src/multi-chat/public/images/logo_taide.jpg" height="100px"></a>
<a href="https://www.nstc.gov.tw/"><img src="./src/multi-chat/public/images/logo_NSTCpng.jpg" height="100px"></a>
<a href="https://www.narlabs.org.tw/"><img src="./src/multi-chat/public/images/logo_NARlabs.jpg" height="100px"></a>
<a href="https://aiacademy.tw/"><img src="./src/multi-chat/public/images/logo_AIA.jpg" height="100px"></a>


## License
[MIT](./LICENSE)
