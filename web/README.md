# API Usage Guide

Welcome to the API usage guide for our service! This guide will help you understand how to interact with our API using various programming languages. Our API allows you to send chat messages and receive model-generated responses, supporting multiple rounds of chatting.

## API Endpoint

The base URL for our API endpoint is:
```
http://localhost/v1.0/chat/completions
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

### Using `curl`

You can use the `curl` command line tool to make POST requests to our API. Here's an example of how to send a single message using `curl`:

```bash
curl -X POST -H "Content-Type: application/json" -H "Authorization: Bearer YOUR_AUTH_TOKEN" -d '{
    "messages": [
        { "isbot": "false", "msg": "你好" }
    ],
    "model": "llama2-7b-chat-b5.0.0"
}' http://localhost/v1.0/chat/completions
```

### Using JavaScript (Ajax)

You can also use JavaScript and the `fetch` API to send a single message to our API.
```javascript
// Define the request payload as an object.
const requestData = {
    messages: [
        { isbot: "false", msg: "你好" }
    ],
    model: "llama2-7b-chat-b5.0.0"
};

// Define the API endpoint and authentication headers.
const apiUrl = 'http://localhost/v1.0/chat/completions';
const headers = {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer 10d50ac2cd899331f1adaa43a38a03b6ea09e037d7a71783606aae7c6ef38cf5'
};

// Perform the AJAX request using the fetch API.
fetch(apiUrl, {
    method: 'POST',
    headers: headers,
    body: JSON.stringify(requestData)
})
.then(response => {
    if (response.status === 200) {
        return response.json(); // Parse the JSON response when the status code is 200.
    } else {
        throw new Error('Request failed with status: ' + response.status);
    }
})
.then(data => {
    // Handle the response data.
    console.log(data); // Log the response data to the console.
})
.catch(error => {
    // Handle errors.
    console.error('Error:', error);
});

```

### Using Python

Here's an example of how to send a single message using Python and the `requests` library:

```python
import requests

# Define the API endpoint and authentication headers.
api_url = 'http://localhost/v1.0/chat/completions'
headers = {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_AUTH_TOKEN'
}

# Define the request payload as a dictionary for single round chatting.
request_data = {
    "messages": [
        { "isbot": "false", "msg": "你好" }
    ],
    "model": "llama2-7b-chat-b5.0.0"
}

# Perform the HTTP request using the requests library.
response = requests.post(api_url, headers=headers, json=request_data)

if response.status_code == 200:
    data = response.json()
    # Handle the response data.
    print(data)
else:
    print(f'Error: Request failed with status {response.status_code}')
```

### Multiple Rounds Chatting

For multiple rounds of chatting, you can extend the `messages` field with the conversation history. The conversation history includes both user and bot messages, allowing for more interactive conversations. Here's an example:

```json
"messages": [
    { "isbot": "false", "msg": "你好" },
    { "isbot": "true", "msg": "你好，我是一個機器人" },
    { "isbot": "false", "msg": "嗨" }
]
```

You can continue to add user and bot messages to this `messages` array to maintain a dynamic conversation with the model.

## Handling Responses

Once you make a successful request to our API, you will receive a JSON response.

```json
{
  "status": "success",
  "message": "Authentication successful",
  "tokenable_id": 1,
  "name": "Drift",
  "output": "哈囉!我是一個樂於助人的助手,您好!我是身為協助者的角色,竭誠幫助主人公實現其目標或完成任務。您有何需要協助或指引的嗎?請別擔心地問,我竭誠照顧您的需求。"
}
```

You can then handle the response data as needed in your application.
