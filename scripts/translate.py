import os
import json
import fire
import requests

def invoke_model(prompt, model="gemini-pro", base_url="https://chatdev.gai.tw"):

    url = f"{base_url}/v1.0/chat/completions"
    auth_token = os.environ["KUWA_AUTH_TOKEN"]
    headers = {
        "Content-Type": "application/json",
        "Authorization": f"Bearer {auth_token}",
    }
    request_body = {
        "messages": [{"isbot": False, "msg": prompt,}],
        "model": model,
    }

    with requests.post(url, headers=headers, json=request_body, stream=True, timeout=60) as resp:
        if not resp.ok:
            raise RuntimeError(f'Request failed with status {response.status_code}')
        for line in resp.iter_lines(decode_unicode=True):
            if not line or line == "event: end": break
            elif line.startswith("data: "):
                chunk = json.loads(line[len("data: "):])["choices"][0]["delta"]["content"]
                yield chunk

def translate(input_filename, target_filename=None, lang="en", quite=False):
    prompt = {
        "en": "Translate to English.",
        "zh-tw": "翻譯成繁體中文。"
    }[lang]

    input_content = ''
    with open(input_filename,encoding="utf-8") as f:
        input_content = f.read()
    prompt += "\n" + input_content

    output_content = ''
    for chunk in invoke_model(prompt):
        output_content += chunk
        if not quite: print(chunk, end='', flush=True)
    
    if not target_filename: exit(0)

    with open(target_filename, 'a',encoding="utf-8") as f:
        f.write(output_content)

if __name__ == '__main__':
  fire.Fire(translate)