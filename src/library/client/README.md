## Kuwa Client Library

This Python library simplifies interaction with the Kuwa GenAI OS APIs.

> [!WARNING]  
> This library is currently under active development and its interface may change in future releases.

## Installation

```bash
cd genai-os/src/library/client
pip install .
```

## Examples

### Setup

First, import the necessary class and instantiate the client:

```python
from kuwa.client import KuwaClient

client = KuwaClient(
    base_url="http://localhost",
    kernel_base_url="http://localhost:9000",
    model="geminipro",
    auth_token="YOUR_API_TOKEN_HERE"
)
```

This code snippet imports the `KuwaClient` and creates an instance configured to connect to a local Kuwa GenAI OS instance using the "geminipro" model. Remember to replace `"YOUR_API_TOKEN_HERE"` with your actual API token.

### Using the Chat API

The following example demonstrates how to use the chat completion feature:

```python
from kuwa.client import KuwaClient
import asyncio

messages = [
    {"role": "user", "content": "Hi"}
]

async def main():
    async for chunk in client.chat_complete(messages=messages):
        print(chunk, end='')
    print()

asyncio.run(main())
```

This example defines a list of messages representing a conversation and uses the `chat_complete` method to stream responses from the model. The `async for` loop iterates over chunks of the response and prints them as they arrive.

### Creating a Base Model

This example shows how to create a new base model:

```python
from kuwa.client import KuwaClient
import asyncio

async def main():
    try:
        response = await client.create_base_model(
            name="API_TEST_MODEL",
            access_code="API_TEST_MODEL",
            order=1,
        )
        print("Model created:", response)
    except Exception as e:
        print("Failed to create model:", e)

asyncio.run(main())
```

Here, the `create_base_model` method sends a request to create a new base model with the specified name, access code, and order.

### Creating a Bot with a Base Model

This example demonstrates creating a new bot and linking it to a previously created base model:

```python
from kuwa.client import KuwaClient
import asyncio

async def main():
    try:
        response = await client.create_base_model(
            name="API_TEST_MODEL",
            access_code="API_TEST_MODEL",
            order=1,
        )
        print("Model created:", response)

        response = await client.create_bot(
            bot_name="API_TEST_MODEL_BOT",
            llm_access_code="API_TEST_MODEL",
        )
        print("Bot created:", response)
    except Exception as e:
        print("An error occurred:", e)

asyncio.run(main())
```

This example first creates a new base model and then uses the `create_bot` method to create a new bot that utilizes the newly created base model.

### Doing web action by Kuwa Token
```python
from kuwa.client import KuwaClient, ModelOperations, BotOperations, RoomOperations, FileOperations
client = KuwaClient(base_url="http://localhost/", auth_token='YOUR_TOKEN_HERE')

# Operation methods
models = ModelOperations(base_url=client.base_url, auth_token=client.auth_token)
bots = BotOperations(base_url=client.base_url, auth_token=client.auth_token)
rooms = RoomOperations(base_url=client.base_url, auth_token=client.auth_token)
files = FileOperations(base_url=client.base_url, auth_token=client.auth_token)

# Create a base model
response = models.create_base_model(name="My BaseModel", access_code="abc123")
print(response)
base_model_id = response["last_llm_id"]
# Create two bots
response = bots.create_bot(llm_access_code="abc123", bot_name="My Bot 1")
print(response)
bot_1 = response["last_bot_id"]
response = bots.create_bot(llm_access_code="abc123", bot_name="My Bot 2")
print(response)
bot_2 = response["last_bot_id"]

# List base models
response = models.list_base_models()
print(response)

# List bots
response = bots.list_bots()
print(response)

# List rooms
response = rooms.list_rooms()
print(response)

# Create a room with bot IDs
bot_ids = [int(i) for i in [bot_1, bot_2]]
response = rooms.create_room(bot_ids=bot_ids)
print(response)

# Delete a room by its ID
room_id = response["result"]
response = rooms.delete_room(room_id=int(room_id))
print(response)

# Upload a file
file_path = "test_file.txt"
response = files.upload_file(file_path=file_path)
print(response)
```

These examples showcase the basic usage of the Kuwa Client Library. For more advanced features and detailed documentation, please refer to the official documentation.
