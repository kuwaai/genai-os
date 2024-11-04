import os
from kuwa.client import KuwaClient
import asyncio
from tqdm.asyncio import tqdm

# Configure the list of specific files to read
files_to_read = ["manage.php"]

# Configure the list of target languages
languages = ["cs_cz", "de", "en_us", "fr_fr","ja_jp", "ko_kr", "zh_cn"]

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

async def translate(content, target_language, file_name, semaphore, progress_bar):
    async with semaphore:
        while True:
            try:
                result = ""
                async for chunk in client.chat_complete(messages=[{"role": "user", "content": 
                    f"This is laravel lang php file, Please translate this lang file into {target_language} language with no comments.\n\n{content}"}]):
                    result += chunk
                result = result.strip().replace("```php", "").replace("```", "").strip()

                if result != "429 Resource has been exhausted (e.g. check quota).":
                    break
                print('Rate limited: waiting and retrying...')
                time.sleep(2)
            except Exception as e:
                print(e)
                print('Retry processing in 5 seconds...')
                time.sleep(5)
        
        target_lang_folder = os.path.join(destination, target_language)
        os.makedirs(target_lang_folder, exist_ok=True)

        destination_file = os.path.join(target_lang_folder, file_name)
        with open(destination_file, 'w', encoding='utf-8') as file:
            file.write(result)

        # Update the progress bar after a task is completed
        progress_bar.update(1)

        return f"Translated content to {target_language}"

source = "../../src/multi-chat/lang/zh_tw"
destination = "translated"
os.makedirs(destination, exist_ok=True)

async def main():
    semaphore = asyncio.Semaphore(5)  # Limit to 5 concurrent tasks
    tasks = []
    total_tasks = len(files_to_read) * len(languages)  # Total number of translation jobs
    
    # Set up the progress bar with the total number of jobs
    progress_bar = tqdm(total=total_tasks, desc="Translating files", unit="job")

    for file_name in files_to_read:
        file_path = os.path.join(source, file_name)
        if os.path.isfile(file_path):
            with open(file_path, 'r', encoding='utf-8') as file:
                content = file.read()
                
            tasks += [translate(content, lang, file_name, semaphore, progress_bar) for lang in languages]
        else:
            print(f"File {file_name} not found in {source}")
    
    await asyncio.gather(*tasks)
    progress_bar.close()
    print("\nAll translations finished.")

asyncio.run(main())
