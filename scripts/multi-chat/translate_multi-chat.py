import os, time, subprocess, re
from kuwa.client import KuwaClient
import asyncio
from tqdm.asyncio import tqdm
from collections import Counter

# Configure the list of specific files to read; if empty, it will read all files in the source directory
files_to_read = []  # Leave empty to read all files in zh_tw

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

def php_to_json(php_text):
    """
    Converts a Laravel PHP language file text into a JSON object.
    :param php_text: The content of the PHP language file as a string.
    :return: A JSON object (Python dictionary).
    """
    php_dict = {}
    # Update regex to match both single and double quotes around the key and value
    pattern = re.compile(r"['\"](?P<key>.*?)['\"]\s*=>\s*['\"](?P<value>.*?)['\"]\s*,")
    matches = pattern.findall(php_text)
    
    for key, value in matches:
        php_dict[key] = value
    
    return php_dict

def json_to_php(json_obj):
    """
    Converts a JSON object (Python dictionary) into a Laravel PHP language file format,
    sorting by the frequency of the first section in the key.
    :param json_obj: A JSON object (Python dictionary).
    :return: A string formatted as a Laravel PHP language file.
    """
    php_lines = ["<?php", "", "return ["]

    # Count the occurrences of each section (split by period)
    sections = [key.split('.')[0] for key in json_obj.keys()]
    section_counts = Counter(sections)

    # Sort keys based on the frequency of the first section
    sorted_items = sorted(json_obj.items(), key=lambda item: section_counts[item[0].split('.')[0]])

    for key, value in sorted_items:
        # Escape single quotes in keys and values
        escaped_key = key.replace("'", "\'")
        escaped_value = value.replace("'", "\'")
        php_lines.append(f"    '{escaped_key}' => '{escaped_value}',")
    
    php_lines.append("];\n")
    
    return "\n".join(php_lines).strip()

# Function to check PHP syntax
def check_php_syntax(file_path):
    try:
        result = subprocess.run(["php", "-l", file_path], capture_output=True, text=True)
        if result.returncode != 0:
            print(f"Syntax error in {file_path}: {result.stderr.strip()}")
            return False
        return True
    except Exception as e:
        print(f"Error checking syntax for {file_path}: {e}")
        return False

async def translate(zh_tw_path, target_lang_path, output_path, target_language, semaphore, progress_bar):
    translated_target = {}
    with open(zh_tw_path, 'r', encoding='utf-8') as file:
        translations = php_to_json(file.read())
    if os.path.isfile(target_lang_path) and check_php_syntax(target_lang_path):
        # If the target file exist and have no error, read and compare them
        with open(target_lang_path, 'r', encoding='utf-8') as file:
            translated_target = php_to_json(file.read())
    filtered_translations = {key: value for key, value in translations.items() if key not in translated_target}
    existing_translation = {key: value for key, value in translated_target.items() if key in translations}
    if len(filtered_translations) > 0:
        content = json_to_php(filtered_translations)
        async with semaphore:
            attempt = 0
            while True:
                attempt += 1
                try:
                    result = ""
                    async for chunk in client.chat_complete(messages=[{"role": "user", "content": 
                        f"This is a Laravel lang PHP file. Please translate this lang file into {target_language} language with no comments. Translation should stay the closest length they're, I want the same format as it was, including <?php at beginning, don't say words, I want just purely the translated file contents.\n\n{content}"}]):
                        result += chunk
                    result = result.strip().replace("```php", "").replace("```", "").strip()

                    if result != "429 Resource has been exhausted (e.g. check quota).":
                        # Save to file
                        target_lang_folder = os.path.join(destination, target_language)
                        os.makedirs(target_lang_folder, exist_ok=True)
                        with open(output_path, 'w', encoding='utf-8') as file:
                            file.write(result)

                        # Check PHP syntax of the output file
                        if check_php_syntax(output_path):
                            if translated_target:
                                result = json_to_php(existing_translation | php_to_json(result))
                            else:
                                result = json_to_php(php_to_json(result))
                            with open(output_path, 'w', encoding='utf-8') as file:
                                file.write(result)
                            if check_php_syntax(output_path): break
                        else:
                            print(f"Retrying translation for {output_path} in {target_language} (attempt {attempt}) due to syntax error...")
                except Exception as e:
                    print(e)
                    print('Retry processing in 5 seconds...')
                    time.sleep(5)
    # Update the progress bar after a successful translation
    progress_bar.update(1)
    return f"Translated content to {target_language}"

base_lang = 'zh_tw'
source = "../../src/multi-chat/lang/"
destination = "translated"
os.makedirs(destination, exist_ok=True)

async def main():
    semaphore = asyncio.Semaphore(5)  # Limit to 5 concurrent tasks
    
    # If files_to_read is empty, get all files from the source directory
    if not files_to_read:
        files_to_read.extend([f for f in os.listdir(source + base_lang) if os.path.isfile(os.path.join(source + base_lang, f))])
    
    tasks = []
    total_tasks = len(files_to_read) * len(languages)  # Total number of translation jobs
    
    # Set up the progress bar with the total number of jobs
    progress_bar = tqdm(total=total_tasks, desc="Translating files", unit="job")

    for file_name in files_to_read:
        zh_tw_file = os.path.join(source + base_lang, file_name)
        if os.path.isfile(zh_tw_file):
            if check_php_syntax(zh_tw_file):
                # Make sure zh_tw have no error
                tasks += [translate(zh_tw_file, os.path.join(source + lang, file_name), os.path.join('translated', lang ,file_name), lang, semaphore, progress_bar) for lang in languages]
        else:
            print(f"File {file_name} not found in {source}")
    
    await asyncio.gather(*tasks)
    progress_bar.close()
    print("\nAll translations finished.")
    input()

asyncio.run(main())
