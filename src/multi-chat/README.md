# TAIDE Chat 0.1.0.1
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

### How to update
1. Stash all your changes by using `git stash`
2. Pull the newest version of files by using `git pull`
3. Go under the folder `cd executables/sh`
4. Run the script `./production_update.sh`

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

### Using JavaScript

You can also use JavaScript and the `fetch` API to send message to our API.
```javascript
// Define the data to be sent in the request
const requestData = {
    messages: [
        { isbot: false, msg: "請自我介紹" } // Requesting a self-introduction
    ],
    model: "gemini-pro" // Using the Gemini Pro model
};

// API endpoint for the request
const apiUrl = 'http://localhost/v1.0/chat/completions';

// Headers for the request, including authorization
const headers = {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_AUTH_TOKEN'
};

// Initialize an empty output variable
var output = ""

// Make a POST request to the API
fetch(apiUrl, {
    method: 'POST',
    headers: headers,
    body: JSON.stringify(requestData) // Send the requestData as JSON
})
.then(response => {
    if (!response.body) {
        throw new Error('ReadableStream not supported');
    }

    // Get a ReadableStream from the response
    const reader = response.body.getReader();

    let buffer = "";
    function readStream() {
        return reader.read().then(({ done, value }) => {
            if (done) {
                console.log("Stream ended");
                return;
            }

            const chunk = new TextDecoder().decode(value);

            buffer += chunk;

            // Process the buffer in chunks until it contains complete JSON objects
            while (buffer.includes("}\n")) {
                const endIndex = buffer.indexOf("}\n") + 2;
                const jsonStr = buffer.substring(6, endIndex);
                buffer = buffer.substring(endIndex);

                try {
                    // Parse the JSON and extract the content
                    const data = JSON.parse(jsonStr)["choices"][0]["delta"]["content"];
                    output += data;
                    console.clear(); // Clear the console
                    console.log(output); // Output the result to the console
                } catch (error) {
                    console.error('Error parsing JSON:', error);
                }
            }

            return readStream();
        });
    }

    return readStream();
})
.catch(error => {
    console.error('Error:', error);
});
```

### Using Python

Here's an example of how to send a single message using Python and the `requests` library:

```python
import requests, json

# Define the API endpoint and authentication headers.
api_url = 'http://127.0.0.1/v1.0/chat/completions'
headers = {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_AUTH_TOKEN'
}

# Define the request payload as a dictionary for single round chatting.
request_data = {
    "messages": [
        { "isbot": False, "msg": "請自我介紹" }
    ],
    "model": "gemini-pro"
}

# Perform the HTTP request using the requests library.
with requests.post(api_url, headers=headers, json=request_data, stream=True,timeout=60) as response:
    for line in response.iter_lines(decode_unicode=True):
        if line:
            if line == "event: end":
                break
            elif line.startswith("data: "):
                try:
                    tmp = json.loads(line[len("data: "):])["choices"][0]["delta"]["content"]
                    print(end=tmp[-1], flush=True)
                except Exception as e:
                    print(e)
```

### Multiple Rounds Chatting

For multiple rounds of chatting, you can extend the `messages` field with the conversation history. The conversation history includes both user and bot messages, allowing for more interactive conversations. Here's an example:

```json
"messages": [
    { "isbot": false, "msg": "你好" },
    { "isbot": true, "msg": "你好，我是一個機器人" },
    { "isbot": false, "msg": "嗨" }
]
```

You can continue to add user and bot messages to this `messages` array to maintain a dynamic conversation with the model.

## Handling Responses

Once you make a successful request to our API, you will receive multiple streaming JSON response like this.

```json
{
    "choices": [
        {
            "delta": {
                "content": "你",
                "role": null
            }
        }
    ],
    "created": 1705602791,
    "id": "chatcmpl-xxxxx",
    "model": "gemini-pro",
    "object": "chat.completion",
    "usage": []
}
{
    "choices": [
        {
            "delta": {
                "content": "好",
                "role": null
            }
        }
    ],
    "created": 1705602791,
    "id": "chatcmpl-xxxxx",
    "model": "gemini-pro",
    "object": "chat.completion",
    "usage": []
}
{
    "choices": [
        {
            "delta": {
                "content": "，",
                "role": null
            }
        }
    ],
    "created": 1705602791,
    "id": "chatcmpl-xxxxx",
    "model": "gemini-pro",
    "object": "chat.completion",
    "usage": []
}
{
    "choices": [
        {
            "delta": {
                "content": "我",
                "role": null
            }
        }
    ],
    "created": 1705602791,
    "id": "chatcmpl-xxxxx",
    "model": "gemini-pro",
    "object": "chat.completion",
    "usage": []
}
...
```

You can then handle the response data as needed in your application.
