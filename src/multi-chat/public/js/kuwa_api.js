class KuwaClient {
    constructor(authToken, baseUrl = "http://localhost") {
        if (!authToken) {
            throw new Error("You must provide an authToken!");
        }
        this.authToken = authToken;
        this.baseUrl = baseUrl;
    }

    async createBaseModel(name, accessCode, options = {}) {
        const url = `${this.baseUrl}/api/user/create/base_model`;
        const headers = {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${this.authToken}`,
        };
        const requestBody = {
            name,
            access_code: accessCode,
            ...options
        };

        const response = await this._makeRequest(url, "POST", headers, JSON.stringify(requestBody));
        return response;
    }

    async listBaseModels() {
        const url = `${this.baseUrl}/api/user/read/models`;
        const headers = {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${this.authToken}`,
        };

        const response = await this._makeRequest(url, "GET", headers);
        return response;
    }

    async listBots() {
        const url = `${this.baseUrl}/api/user/read/bots`;
        const headers = {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${this.authToken}`,
        };

        const response = await this._makeRequest(url, "GET", headers);
        return response;
    }
    async listRooms() {
        const url = `${this.baseUrl}/api/user/read/rooms`;
        const headers = {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${this.authToken}`,
        };

        const response = await this._makeRequest(url, "GET", headers);
        return response;
    }
    async createRoom(bot_ids) {
        const url = `${this.baseUrl}/api/user/create/room`;
        const headers = {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${this.authToken}`,
        };
        const requestBody = {
            llm: bot_ids
        };

        const response = await this._makeRequest(url, "POST", headers, JSON.stringify(requestBody));
        return response;
    }
    async uploadFile(file) {
        try {
            const url = `${this.baseUrl}/api/user/upload/file`;
            const headers = {
                "Authorization": `Bearer ${this.authToken}`,
            };
            const formData = new FormData();
            formData.append('file', file);
            const response = await this._makeRequest(url, "POST", headers, formData);
            return response;
        } catch (error) {
            console.error('Error uploading file:', error);
        }
    }
    async deleteRoom(room_id) {
        const url = `${this.baseUrl}/api/user/delete/room/`;
        const headers = {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${this.authToken}`,
        };
        const requestBody = {
            id: room_id
        };

        const response = await this._makeRequest(url, "DELETE", headers, JSON.stringify(requestBody));
        return response;
    }

    async createBot(llmAccessCode, botName, options = {}) {
        const url = `${this.baseUrl}/api/user/create/bot`;
        const headers = {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${this.authToken}`,
        };
        const requestBody = {
            llm_access_code: llmAccessCode,
            bot_name: botName,
            visibility: 3, // Default visibility
            ...options
        };

        const response = await this._makeRequest(url, "POST", headers, JSON.stringify(requestBody));
        return response;
    }

    async *chatCompleteAsync(model, messages = [], options = {}) {
        // This is streaming method
        const url = `${this.baseUrl}/v1.0/chat/completions`;
        const headers = {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${this.authToken}`,
        };
        const requestBody = {
            messages,
            model,
            stream: true,
            ...options
        };

        const response = await fetch(url, {
            method: "POST",
            headers,
            body: JSON.stringify(requestBody)
        });

        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`);
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder("utf-8");

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            const chunk = decoder.decode(value);
            const lines = chunk.split('\n').filter(line => line);

            for (const line of lines) {
                if (line === "data: [DONE]") break;
                if (line.startsWith("data: ")) {
                    const chunkContent = JSON.parse(line.substring("data: ".length))["choices"][0]["delta"];
                    if (chunkContent?.content) {
                        yield chunkContent.content;
                    }
                }
            }
        }
    }

    chatComplete(model, messages = [], options = {}) {
        // This is non-streaming method.
        const url = `${this.baseUrl}/v1.0/chat/completions`;
        const requestBody = {
            messages,
            model,
            stream: false,
            ...options
        };

        const xhr = new XMLHttpRequest();
        xhr.open("POST", url, false);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.setRequestHeader("Authorization", `Bearer ${this.authToken}`);

        xhr.send(JSON.stringify(requestBody));

        if (xhr.status !== 200) {
            throw new Error(`Request failed with status ${xhr.status}`);
        }

        const response = JSON.parse(xhr.responseText);
        return response;
    }

    async _makeRequest(url, method, headers, body) {
        const response = await fetch(url, {
            method,
            headers,
            body: body
        });

        if (!response.ok) {
            const errorDetails = await response.json();
            throw new Error(`Request failed with status ${response.status}, ${JSON.stringify(errorDetails)}`);
        }
        return await response.json();
    }
}

/*
Kuwa Chat complete example

-- Init --
const client = new KuwaClient("YOUR_API_TOKEN_HERE","http://localhost");

-- Streaming --
const messages = [{ role: "user", content: "hi" }];
(async () => {
    try {
        for await (const chunk of client.chatCompleteAsync("geminipro",messages)) {
            console.log(chunk);
        }
    } catch (error) {
        console.error(error.message);
    }
})();

-- Non-Streaming --
const messages = [{ role: "user", content: "hi" }];
const result = client.chatComplete("geminipro",messages);
console.log(result);
console.log(result.choices[0].message.content)

-- Create Base Model --
client.createBaseModel('test2', 'test_code2')
    .then(response => console.log('Base Model Created:', response))
    .catch(error => console.error('Error:', error));

-- List Base Models --
client.listBaseModels()
    .then(response => console.log(response))
    .catch(error => console.error('Error:', error));

-- List Bots --
client.listBots()
    .then(response => console.log(response))
    .catch(error => console.error('Error:', error));

-- Create Room --
client.createRoom([1,2,3])
    .then(response => console.log(response))
    .catch(error => console.error('Error:', error));

-- Delete Room --
client.deleteRoom(1)
    .then(response => console.log(response))
    .catch(error => console.error('Error:', error));

-- Read Room list --
client.listRooms()
    .then(response => console.log(response))
    .catch(error => console.error('Error:', error));

-- Simple interface for uploading file --
document.documentElement.innerHTML = '';
const fileInput = document.createElement('input');
fileInput.type = 'file';
fileInput.id = 'test';
const uploadButton = document.createElement('button');
uploadButton.textContent = 'Upload File';
document.body.appendChild(fileInput);
document.body.appendChild(uploadButton);
uploadButton.addEventListener('click', () => {
    const file = fileInput.files[0];
    if (file) {
        client.uploadFile(file)
        .then((response) => {
            const result = document.createElement('p')
            result.textContent = JSON.stringify(response)
            document.body.appendChild(result)
            console.log(response)
            })
        .catch(error => console.error('Error:', error));
    } else {
        alert('Please select a file.');
    }
});

*/
