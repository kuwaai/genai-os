[正體中文Readme.md](./README_TW.md)

<h1 align="center">
  <br>
  <a href="https://dev.kuwaai.org/">
  <img src="./multi-chat/web/public/images/kuwa.png" alt="Kuwa GenAI OS" width="200"></a>
  <br>
  Kuwa GenAI OS
  <br>
</h1>

<h4 align="center">An open, free, secure, and privacy-focused Generative-AI Operating System.</h4>

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
  <a href="#key-features">Key Features</a> •
  <a href="#dependencies">Architecture</a> •
  <a href="#installation-guide">Installation Guide</a> •
  <a href="#community">Community</a> •
  <a href="#acknowledgements">Acknowledgements</a> •
  <a href="#license">License</a>
</p>

![screenshot](./multi-chat/web/public/images/demo.gif)

## Architecture
> **WARNING**: This draft is preliminary and subject to further changes.

[![screenshot](./multi-chat/web/public/images/architecture.svg)](https://kuwaai.org/os/Intro)

## Key Features
* Multi-lingual Support: Kuwa GenAI OS provides a turnkey solution for GenAI development and deployment in multiple languages.

* Advanced Chat Features: Users can enjoy concurrent multi-chat, quoting, full prompt-list import/export/share, and more.

* Flexible Prompt Orchestration: Kuwa GenAI OS allows flexible orchestration of prompts, RAGs, bots, models, and hardware/GPUs.

* Hardware Support: Supports heterogeneous hardware from virtual hosts, laptops, PCs, servers to the cloud.

* Dark/Light Mode: Offers both dark and light mode for user preference.

* Cross-Platform Compatibility: Kuwa GenAI OS is now compatible with Windows, and Linux, making it accessible across different platforms.

* Open-Source: Kuwa GenAI OS is an open-source project, allowing developers to contribute and customize the system to their needs.

## Dependencies

To run this application, ensure you have the following dependencies installed on your system:

- Node.js v20.11.1 & npm
- PHP 8.1.27 & php-fpm & Composer
- Python 3.9.5 & pip
- Nginx or Apache
- Redis 6.0.20
- CUDA
- Git

Please follow these steps to set up and run the project on both Windows and Linux:

## Installation Guide
Before continue, Please make sure you have installed all the dependency programs above.
1. **Clone the repository:**
   ```sh
   git clone https://github.com/kuwaai/gai-os.git
   cd gai-os/multi-chat/web/
   ```

2. **Install dependencies:**

   - For Linux:
     ```sh
     cp .env.dev .env
     cd executable/sh
     ./production_update.sh
     cd LLMs/agent
     pip install -r requirement.txt
     ```

   - For Windows:
     ```bat
     copy .env.dev .env
     cd executable/bat
     ./production_update.bat
     cd LLMs/agent
     pip install -r requirement.txt
     ```

3. **Configure PHP and PHP-FPM:**
   - Make sure PHP is installed and configured correctly.
   - Configure your web server (Nginx or Apache) to serve the project's files. Set `multi-chat/web/public` as the html root.
   - Example setting file: `multi-chat/web/nginx_config_example`, `multi-chat/web/php.ini`
   - Recommended settings:
     - Set PHP max upload filesize to at least 10MB for RAG.
     - Set timeout of reading to at least 120 seconds or more for long-running models.

4. **Configure Redis:**
   - Ensure Redis Server is installed and running.
   - Configure it in `.env` or use default settings.
   - Start Redis worker by running `php artisan queue:work --timeout=0` under `multi-chat/web/` to process requests to the agent, It's recommanded to have at least 5 workers running at the same time.

5. **Run the application:**
   - Start your web server and PHP-FPM.
   - Run the agent in `multi-chat/LLMs/agent/main.py` with your Python installation. It's recommended to copy the agent folder to another location before execution.

6. **Access the application:**
   - First you need to seed a account, go `multi-chat/web/` and run `php artisan db:seed --class=AdminSeeder --force` to seed your first admin account.
   - Open your web browser and go to the application's URL.
   - Login with your admin account and start using Kuwa GenAI OS

This should get your project up and running.

## Download

You can [download](https://github.com/kuwaai/gai-os/releases) the latest release of Kuwa GenAI OS for Windows and Linux.

## Community

[Discord](https://discord.gg/4HxYAkvdu5) - Kuwa AI Discord community server

[Facebook](https://www.facebook.com/groups/g.kuwaai.org) - Kuwa AI Community

[Facebook](https://www.facebook.com/groups/g.kuwaai.tw) - Kuwa AI 臺灣社群

[Google Group](https://groups.google.com/g/kuwa-dev) - kuwa-dev

## Announcement

[Facebook](https://www.facebook.com/kuwaai) - Kuwa AI

[Google Group](https://groups.google.com/g/kuwa-announce) - kuwa-announce

## Support

We're a small team of two, passionate about our project. If you're interested in what we've built, we'd love your contributions to help make our open-source project even better and more robust. Please don't hesitate to reach out if you're willing to lend a hand!

## Credits

This software uses the following packages and programs:

- [PHP & PHP-FPM](https://www.php.net/)
- [Laravel 10](https://laravel.com/)
- [Python 3](https://www.python.org/)
- [Node.js](https://nodejs.org/)
- [Docker](https://www.docker.com/)
- [Redis](https://redis.io/)
- [Marked](https://github.com/chjj/marked)
- [highlight.js](https://highlightjs.org/)
- [Nvidia CUDA](https://developer.nvidia.com/cuda-toolkit)

## Acknowledgements
Many thanks to Taiwan NSTC TAIDE project and AI Academy for their early supports to this project.
<a href="https://www.nuk.edu.tw/"><img src="./multi-chat/web/public/images/logo_NUK.jpg" height="100px"></a>
<a href="https://taide.tw/"><img src="./multi-chat/web/public/images/logo_taide.jpg" height="100px"></a>
<a href="https://www.nstc.gov.tw/"><img src="./multi-chat/web/public/images/logo_NSTCpng.jpg" height="100px"></a>
<a href="https://www.narlabs.org.tw/"><img src="./multi-chat/web/public/images/logo_NARlabs.jpg" height="100px"></a>
<a href="https://aiacademy.tw/"><img src="./multi-chat/web/public/images/logo_AIA.png" height="100px"></a>

## License
[MIT](./LICENSE)