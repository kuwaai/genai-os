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
    model="gemini-pro",
    auth_token="YOUR_API_TOKEN_HERE"
)
```

This code snippet imports the `KuwaClient` and creates an instance configured to connect to a local Kuwa GenAI OS instance using the "gemini-pro" model. Remember to replace `"YOUR_API_TOKEN_HERE"` with your actual API token.

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

These examples showcase the basic usage of the Kuwa Client Library. For more advanced features and detailed documentation, please refer to the official documentation.
