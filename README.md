# TAIDE Chat 0.0.4.5
### Basic Software Requirements
* PostgreSQL 14
* Nodejs 18
* PHP & PHP-FPM 8.1
* Redis Server
* Vite (Use `npm install -g vite`)
### How to setup Docker enviroment
1. Clone the whole project
2. Run the commands below to download required packages on the machine you want to use
```shell
#Please be aware that the last command will make the machine reboot!
curl -s -L https://nvidia.github.io/nvidia-docker/gpgkey | sudo apt-key add -
distribution=$(. /etc/os-release;echo $ID$VERSION_ID)
curl -s -L https://nvidia.github.io/nvidia-docker/$distribution/nvidia-docker.list | sudo tee /etc/apt/sources.list.d/nvidia-docker.list
sudo apt update
sudo apt install -y docker docker.io nvidia-docker2 docker-compose ubuntu-drivers-common
sudo systemctl daemon-reload
sudo systemctl restart docker
sudo ubuntu-drivers install
wget https://developer.download.nvidia.com/compute/cuda/repos/ubuntu2204/x86_64/cuda-keyring_1.0-1_all.deb
sudo dpkg -i cuda-keyring_1.0-1_all.deb
sudo apt-get update
sudo apt-get -y install cuda
rm -rf cuda-keyring_1.0-1_all.deb
sudo reboot
```
3. Go inside `Docker/llmproject_image` and run `./build.sh` to build web image
4. Go inside `Docker/API_image` and run `./build.sh` to build LLM API image
5. Back to `Docker` and run `./rebuild.sh` to start the docker-compose
6. You can run `sudo docker container ls -a` to check if the 4 services are all alive and good
7. Go inside the web and here's the 4 default accounts
Role | Developer | Demo1 | Demo2 | Demo3
--- | --- | --- | --- | --- 
Account | dev@chat.gai.tw | demo1@chat.gai.tw | demo2@chat.gai.tw | demo3@chat.gai.tw 
--- | --- | --- | --- | ---
Password | develope | chatchat | chatchat | chatchat 
--- | --- | --- | --- | ---
isDemo | False | True | True | True 
8. Your web should goes online and you're able to view the web at `localhost:8080`
9. This setup contain one default Debug LLM API, You need to add it to dashboard in order to use it, Just create a LLM Profile with `debug` as `ACCESS CODE`

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
3. Run the script `./production_update.sh`

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