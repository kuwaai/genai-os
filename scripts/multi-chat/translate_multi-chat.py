import os, time, subprocess
from kuwa.client import KuwaClient
import asyncio
from tqdm.asyncio import tqdm

# Configure the list of specific files to read; if empty, it will read all files in the source directory
files_to_read = ['room.php', 'executors.php']  # Leave empty to read all files in zh_tw

# Configure the list of target languages
languages = ["cs_cz", "de", "en_us", "fr_fr", "ja_jp", "ko_kr", "zh_cn"]

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

# Function to check PHP syntax
def check_php_syntax(file_path):
    try:
        result = subprocess.run(["php", "-l", file_path], capture_output=True, text=True)
        if result.returncode != 0:
            print(f"Syntax error in {file_path}: {result.stderr.strip()}")
            return False
        print(f"No syntax errors detected in {file_path}")
        return True
    except Exception as e:
        print(f"Error checking syntax for {file_path}: {e}")
        return False

async def translate(content, target_language, file_name, semaphore, progress_bar):
    async with semaphore:
        attempt = 0
        while True:
            attempt += 1
            try:
                result = ""
                async for chunk in client.chat_complete(messages=[{"role": "user", "content": 
                    f"This is a Laravel lang PHP file. Please translate this lang file into {target_language} language with no comments.\n\n{content}"}]):
                    result += chunk
                result = result.strip().replace("```php", "").replace("```", "").strip()

                if result != "429 Resource has been exhausted (e.g. check quota).":
                    # Save to file
                    target_lang_folder = os.path.join(destination, target_language)
                    os.makedirs(target_lang_folder, exist_ok=True)
                    destination_file = os.path.join(target_lang_folder, file_name)
                    with open(destination_file, 'w', encoding='utf-8') as file:
                        file.write(result)

                    # Check PHP syntax of the output file
                    if check_php_syntax(destination_file):
                        break  # Syntax is correct, exit loop
                    else:
                        print(f"Retrying translation for {file_name} in {target_language} (attempt {attempt}) due to syntax error...")
            except Exception as e:
                print(e)
                print('Retry processing in 5 seconds...')
                time.sleep(5)
        
        # Update the progress bar after a successful translation
        progress_bar.update(1)
        return f"Translated content to {target_language}"

source = "../../src/multi-chat/lang/zh_tw"
destination = "translated"
os.makedirs(destination, exist_ok=True)

async def main():
    semaphore = asyncio.Semaphore(5)  # Limit to 5 concurrent tasks
    
    # If files_to_read is empty, get all files from the source directory
    if not files_to_read:
        files_to_read.extend([f for f in os.listdir(source) if os.path.isfile(os.path.join(source, f))])
    
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
    input()

asyncio.run(main())
