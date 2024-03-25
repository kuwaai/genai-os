# TAIDE Chat 0.2.0.0
### Implements
* [Finished] One or more LLMs chatting
* [Finished] LLM management and internal API with proxy
* [Finished] User sign up & login, email auth, LDAP auth, api with user key
* [Finished] Group & Permission & User management
* [Finished] Import & Export Chatrooms via TSV or JSON
* [Finished] Import multiple prompt to quick inference
* [Finished] Feedback & train data export
* [Finished] Soft delete except account deletion
* [Finished] Translation and quote react button
* [Finished] Mobile UI
* [Finished] Markdown output supported
* [Finished] Highlight the input output prompt
* [Finished] Blinded model feedback testing
* [WIP] Regenerate & Edit message button
* [WIP] Rule-based control in Model worker
* [WIP] Bot stores
* [WIP] A fully working API
* [WIP] Live streaming chat
* [WIP] Auto scaling models
* [WIP] ODT chat history export
* [WIP] System stress measure


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
git clone https://github.com/kuwaai/genai-os.git
sudo cp -r genai-os/multi-chat /var/www/html/
cd /var/www/html
sudo chown ubuntu:ubuntu -R LLMProject 
cd /var/www/html/multi-chat
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
sudo cp /var/www/html/multi-chat/www.conf /etc/php/8.1/fpm/pool.d/
cd /etc/nginx/sites-enabled
sudo cp /var/www/html/multi-chat/nginx_config ../sites-available/multi-chat
sudo ln -s ../sites-available/multi-chat .
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
![arch](demo/arch.png?raw=true "Architecture to complete jobs")

# API Usage Guide

Welcome to the API usage guide for our service! This guide will help you understand how to interact with our API using various programming languages. Our API allows you to send chat messages and receive model-generated responses, supporting multiple rounds of chatting.

## API Endpoint

The base URL for our API endpoint is:
```
http://127.0.0.1/v1.0/chat/completions
```

## Authentication

To access our API, you need to include an `Authorization` header in your requests. You should provide an authentication token in the following format:
```
Bearer YOUR_AUTH_TOKEN
```

Please note that you must have the necessary permissions to use this API, including the "read_api_token" permission to read your authentication token. If you don't have this permission, please contact your administrator or the API provider to ensure you have the required access.

To retrieve your authentication token, you can visit your profile page on our platform, where you'll find the token associated with your account.

Replace `YOUR_AUTH_TOKEN` with your unique authentication token. This token is used to authenticate your requests.

## Sending Messages

### Single Round Chatting

For single round chatting, you can send a single message using the `messages` field. Here's an example:

### Using `curl` (Linux)

You can use the `curl` command line tool to make POST requests to our API. Here's an example of how to send a single message using `curl`:

```bash
curl -X POST -H "Content-Type: application/json" -H "Authorization: Bearer YOUR_AUTH_TOKEN" -d '{
    "messages": [
        { "isbot": false, "msg": "請自我介紹" }
    ],
    "model": "gemini-pro"
}' http://127.0.0.1/v1.0/chat/completions
```

### Using `curl` (Windows)

For Windows you need to escape these characters, here's how to do it:

```bash
curl -X POST -H "Content-Type: application/json" -H "Authorization: Bearer YOUR_AUTH_TOKEN" -d "{\"messages\": [{ \"isbot\": false, \"msg\": \"請自我介紹\" }],\"model\": \"gemini-pro\"}" http://127.0.0.1/v1.0/chat/completions
```

### Using JavaScript (Ajax)

You can also use JavaScript and the `fetch` API to send a single message to our API.
```javascript
// Define the request payload as an object.
const requestData = {
    messages: [
        { isbot: false, msg: "請自我介紹" }
    ],
    model: "gemini-pro"
};

// Define the API endpoint and authentication headers.
const apiUrl = 'http://127.0.0.1/v1.0/chat/completions';
const headers = {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_AUTH_TOKEN'
};

// Perform the AJAX request using the fetch API.
fetch(apiUrl, {
    method: 'POST',
    headers: headers,
    body: JSON.stringify(requestData)
})
.then(response => {
    if (!response.body) {
        throw new Error('ReadableStream not supported');
    }

    // Create a reader for the response stream.
    const reader = response.body.getReader();

    // Function to read the stream and concatenate chunks.
    function readStream(reader, buffer = "") {
        return reader.read().then(({ done, value }) => {
            if (done) {
                // Handle the last chunk and end the stream.
                handleStreamData(buffer);
                return;
            }

            // Convert the chunk to a string.
            const chunk = new TextDecoder().decode(value);

            // Split the chunk into lines.
            const lines = (buffer + chunk).split("data:");

            // Process each line.