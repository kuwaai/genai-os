# TAIDE Chat 0.0.3
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
6. Then run the following commands here to start the web service

For 0.0.3, please use:
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
For 0.0.2, please use:
```shell
screen -dmS worker bash -c "./work.sh"
screen -dmS web bash -c "./startWeb_Public.sh"

screen -dmS bloom1b1zh bash -c "python3 ~/LLMs/0.0.2/Bloom_1b1-zh.py"
screen -dmS dolly bash -c "python3 ~/LLMs/0.0.2/Dolly.py"
screen -dmS llama bash -c "python3 ~/LLMs/0.0.2/LLaMA_TW1.py"
```
### For production
Nginx is recommanded, Since that is the only tested one,
The configure file is provided under the repo and named `nginx_config`.
Remember to use PHP-FPM, for the web I hosted in TWCC,
I have configured it to use maximum of 2048 child processes.

### Setup Redis worker
7. Use another tty session or use `screen`, then execute `./work.sh` so you can start a fresh Redis worker
8. Now you need LLM API, I included some examples inside the folder `LLMs`, the code can be modified to use other LLMs(`LLaMA_TW1.py` require the trained model from 塗子謙)
9. For an easy sample you can begin with `Bloom_1b1-zh.py`, run `screen -dmS bloom bash -c "python3 Bloom_1b1-zh.py"`
10. Wait until it's done, you can use `screen -rx bloom` to attach the terminal, and use `Ctrl+A` then `D` to detach
11. Then you're all done, the service should work if nothing went wrong!
### How it works
For 0.0.3, This is how it works

![arch_0.0.3](demo/arch_0.0.3.png?raw=true "Architecture for 0.0.3")

For 0.0.2, This is how it works

![arch_0.0.2](demo/arch_0.0.2.png?raw=true "Architecture for 0.0.2")
