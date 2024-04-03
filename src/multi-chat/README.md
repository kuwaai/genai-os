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
            lines.forEach(line => {
                if (line.trim() !== "") {
                    // Handle the current line (remove any leading/trailing whitespace).
                    handleStreamData(line.trim());
                }
            });

            // Continue reading the next chunk.
            return readStream(reader, lines[lines.length - 1]);
        });
    }

    // Start reading the stream.
    return readStream(reader);
})
.catch(error => {
    // Handle errors.
    console.error('Error:', error);
});

// Function to handle each data point in the stream.
function handleStreamData(line) {
    if (line === "event: end") {
        // Handle the end of the stream.
        console.log("Stream ended");
        return;
    }

    try {
        const data = JSON.parse(line)["choices"][0]["delta"]["content"];
        console.log(data); 
    } catch (error) {
    }
}
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

Once you make a successful request to our API, you will receive a JSON response like this.

```json
{
    "choices": [
        {
            "delta": {
                "content": "你好，我是由Google開發的大型語言模型，又稱「對話式AI」，或稱「聊天機器人」。我透過文字進行互動，接受過大量資料的訓練，具備學習和理解的能力，能夠回答各種問題、撰寫不同的內容。\n我目前仍然在發展階段，但已經能夠執行多種語言任務，包括以下項目：\n\n* 翻譯語言\n* 回答問題\n* 撰寫故事、詩歌等不同類型的文本\n* 理解和生成程式碼\n* 玩遊戲\n* 提供書寫建議等等\n\n我的目標是成為一個功能強大的工具，幫助人們完成各種任務，並提供有用的資訊。隨著我繼續學習和成長，我希望能越來越好，為人們提供更好的服務。\n\n如果今天想請我幫忙的話，您可以提出您的要求。我將盡力提供您需要且有用的資訊",
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
```

You can then handle the response data as needed in your application.
