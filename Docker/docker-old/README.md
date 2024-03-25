### How to setup your Docker enviroment
1. Clone the whole project to your machine
2. Run the commands below to download required packages for building docker containers

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
3. Use `cd LLM_Project/docker-old` to get inside the docker folder
4. Run `./rebuild.sh` to build the containers
5. Then just run `sudo docker container ls -a` to check if the 4 services are all healthy and good
6. You'll have 4 accounts in default, like the table below

Role | Admin | Demo1 | Demo2 | Demo3
--- | --- | --- | --- | --- 
Account | dev@chat.gai.tw | demo1@chat.gai.tw | demo2@chat.gai.tw | demo3@chat.gai.tw 
Password | develope | chatchat | chatchat | chatchat 
Permission | All | None | None | None 
7. Go inside the web at `http://localhost:8080/` and login with the admin account
8. This setup in default provided you the most basic LLM, the debug LLM, which outputs the input you gave
9. You can modify this architecture to boot more LLMs, But remember to open more Redis worker as you have more LLMs appended, You can find the command to start Redis worker in `LLMProject/docker-old/script/run.sh`, just go ahead append this command `screen -L -dmS worker1 bash -c "cd /var/www/html/LLM_Project/executables/docker/ && ./work.sh"` multiple times until it has the same amount as your LLMs