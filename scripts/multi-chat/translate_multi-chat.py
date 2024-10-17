import os
from kuwa.client import KuwaClient
import asyncio

# Configure the list of specific files to read
files_to_read = ["manage.php"]

# Configure the list of target languages
languages = ["cs_cz", "de", "en_us", "fr_fr","ja_jp", "ko_kr", "zh_cn"]  # Add more languages as needed

def get_key():
    KEY_FILE = '.env'
    if os.path.exists(KEY_FILE):
        with open(KEY_FILE, 'r') as file:
            key = file.read().strip()
            if key:
                print(f"Key found: {key}")
                return key
    
    key = input("Enter your key: ")
    with open(KEY_FILE, 'w') as file:
        file.write(key)
    print(f"Key stored: {key}")
    return key

client = KuwaClient(
    base_url="https://chat.gai.tw",
    model="geminipro",
    auth_token=get_key()
)

async def translate(content, target_language, file_name):
    while True:
        print(f"Start processing {target_language}")
        result = ""
        async for chunk in client.chat_complete(messages=[{"role": "user", "content": 
            f"This is laravel lang php file, Please translate this lang file into {target_language} language.\n\n{content}"}]):
            print(chunk, end='')
            result += chunk
        print()
        result = result.strip().replace("```php", "").replace("```", "").strip()

        if result != "429 Resource has been exhausted (e.g. check quota).":
            # This is for gemini pro
            break
        print('eh, 429')
    # Create the target folder for the language if it doesn't exist
    target_lang_folder = os.path.join(destination, target_language)
    os.makedirs(target_lang_folder, exist_ok=True)

    # Save the translated content into the respective language folder
    destination_file = os.path.join(target_lang_folder, file_name)
    with open(destination_file, 'w', encoding='utf-8') as file:
        file.write(result)

    print(f"Translated content saved to {destination_file}")
    return f"Translated content to {target_language}"

# Define source and destination directories
source = "../../src/multi-chat/lang/zh_tw"
destination = "translated"

# Ensure the destination directory exists
os.makedirs(destination, exist_ok=True)

async def main():
    tasks = []
    # Iterate through the specified files in the zh_tw directory
    for file_name in files_to_read:
        file_path = os.path.join(source, file_name)
        if os.path.isfile(file_path):
            # Read the file content
            with open(file_path, 'r', encoding='utf-8') as file:
                content = file.read()
                
            # Translate the content to each language
            tasks += [translate(content, lang, file_name) for lang in languages]
        else:
            print(f"File {file_name} not found in {source}")
    # Execute all the process at the same time using async
    await asyncio.gather(*tasks)
    print()
    input("All finished, You can hit Enter and leave now.")
    
asyncio.run(main())