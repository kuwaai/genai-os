# TAIDE Chat 0.0.4.4
### Basic Software Requirements
* PostgreSQL 14
* Nodejs 18
* PHP & PHP-FPM 8.1
* Redis Server
* Vite (Use `npm install -g vite`)
### Setup the web
1. Clone this project by using `git clone`
2. Copy environment configure file `cp .env.debug .env`
3. Modify `.env` for your environment
4. Go under the folder `cd executables/sh`
5. Run the script `./install.sh`, this should install most of things you needed.
6. Below are the commands you might need to startup the entire service.

```shell
screen -dmS web bash -c "./startWeb_Public.sh" #This is the web process
screen -dmS agent bash -c "python3 ~/LLMs/0.0.3/agent.py" # This agent is required

#Below is your LLM API and workers, remember for each API, you should open a worker for it

screen -dmS workerForBloom bash -c "cd /var/www/html/LLM_Project/executables/sh/ && ./work.sh"
screen -dmS bloom1b1zh bash -c "python3 ~/LLMs/0.0.3/Bloom_1b1-zh.py"

screen -dmS workerForDolly bash -c "cd /var/www/html/LLM_Project/executables/sh/ && ./work.sh"
screen -dmS dolly bash -c "python3 ~/LLMs/0.0.3/Dolly.py"

screen -dmS workerForLLaMA bash -c "cd /var/www/html/LLM_Project/executables/sh/ && ./work.sh"
screen -dmS llama bash -c "python3 ~/LLMs/0.0.3/LLaMA_TW1.py"
```
### How to update
1. Pull the newest version of files by using `git pull`
2. Go under the folder `cd executables/sh`
3. Run the script `./install.sh` and `./startweb_public.sh`

### For production
Nginx is recommanded, Since that is the only tested one,
The configure file is provided under the repo and named `nginx_config`.
Remember to use PHP-FPM, for the web I hosted in TWCC,
I have configured it to use maximum of 2048 child processes.
Also it's recommanded to modify this variable in php.ini
`default_socket_timeout=60` from 60 to any higher value,
So when the model took too long, it won't shows 504 gateway timeout

### How it works
![arch](demo/arch.png?raw=true "Architecture to complete jobs")