# TAIDE Chat 0.0.7
### Implements
* [Finished] Basic Chatting with just one LLM
* [Finished] Duel Chatting with multiple LLMs
* [Finished] LLM management and internal API with proxy
* [Finished] User sign up & login, email auth, api auth
* [Finished] Group & Permission & User management
* [Finished] A basic working API that just worked
* [Finished] Import & Export Chatroom
* [Finished] Feedback system
* [Finished] Soft delete for chatroom and history
* [WIP] Regeneration & Translation button
* [WIP] Rule-based control in Model worker
* [WIP] Expanded Chatroom list
* [WIP] Import with generate
* [WIP] First play ground game: AI Election
* [WIP] A complete Externel API
* [WIP] Blinded model rating

### Basic Software Requirements
* PostgreSQL 14
* Nodejs 18
* PHP & PHP-FPM 8.1
* Redis Server
* Vite (Use `npm install -g vite`)

### The whole commands to setup the web and database directly
```sh
# Update all packages
sudo apt update
sudo apt upgrade -y
# These are required packages
sudo apt install nginx php php-fpm redis nodejs npm postgresql postgresql-contrib zip unzip php-zip
 -y
#Install hamachi if you need (optional)
wget https://www.vpn.net/installers/logmein-hamachi_2.1.0.203-1_amd64.deb
sudo dpkg -i logmein-hamachi_2.1.0.203-1_amd64.deb
rm logmein-hamachi_2.1.0.203-1_amd64.deb
sudo hamachi login
# Create database (modify the command’s red parts as your require)
sudo -u postgres psql
create database llm_project;
create user llmprojectroot with encrypted password 'LLMProject';
grant all privileges on database llm_project to llmprojectroot;
quit
# Installing ‘n’ package require sudo account
sudo su
npm install n -g
n stable
exit
# After installing, you need to relogin
node -v # Should show you v18.xx.xx version installed
# Time for the github project
git clone git@github.com:taifu9920/LLM_Project.git
sudo mv LLM_Project /var/www/html/
cd /var/www/html
sudo chown ubuntu:ubuntu -R LLMProject 
cd /var/www/html/LLM_Project/web/
cp .env.debug .env
# Now you should edit the .env file before proceed
cd executables/sh
sudo chmod +x *.sh
./production_update.sh
# This step give the file owner back to www-data, so nginx can works
cd /var/www/html
sudo chown www-data:www-data -R LLMProject 
# It should setup most of things, proceed if no errors
# Please make sure the path correct for you before execute
sudo cp /var/www/html/LLM_Project/web/www.conf /etc/php/8.1/fpm/pool.d/
cd /etc/nginx/sites-enabled
sudo cp /var/www/html/LLM_Project/web/nginx_config ../sites-available/LLM_Project
sudo ln -s ../sites-available/LLM_Project .
# Get a ssl cert (optional)
sudo apt install python3-certbot-nginx -y
sudo certbot
# Fill the information and done
# Now the web should be ready
```

### How to update
1. Stash all your changes by using `git stash`
2. Pull the newest version of files by using `git pull`
3. Go under the folder `cd executables/sh`
4. Run the script `./production_update.sh`
(Some updates will required to do migration update, So confirm the migrate is recommanded)

### For production
Nginx is recommanded, Since that is the only tested one,
The configure file is provided under the repo and named `nginx_config`.
Remember to use PHP-FPM, for the web I hosted in TWCC,
I have configured it to use maximum of 2048 child processes.
Also it's recommanded to modify this variable in php.ini
`default_socket_timeout=60` from 60 to any higher value,
So when the model took too long, it won't shows 504 gateway timeout

### How it works
![arch](web/demo/arch.png?raw=true "Architecture to complete jobs")
