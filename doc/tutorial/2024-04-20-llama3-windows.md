---
slug: kuwa-os-v0.2.0-llama3-windows
title: Kuwa v0.2.0 + Llama3 Setup (Windows, including TAIDE version)
authors: [iftnt]
tags: [KuwaOS, v0.2.0-beta, v0.2.0-taide, Llama3]
date: 2024-04-20 23:00:00
---

## Getting the Model

### Method 1: Applying for Access on HuggingFace

1. Log in to HuggingFace and go to https://huggingface.co/meta-llama/Meta-Llama-3-8B-Instruct to apply for access to the meta-llama/Meta-Llama-3-8B-Instruct model (approximately 1 hour for review)
   ![](/blog-img/2024-04-20-llama3/hf-meta-llama3.png)
2. If you see the "You have been granted access to this model" message, it means you have obtained the model access, and you can download the model
   ![](/blog-img/2024-04-20-llama3/hf-meta-llama3-granted.png)

<!-- truncate -->

3. If you need to use a model that requires login, you need to set up the HuggingFace Token. If you are using a model that does not require login, you can skip this step
    Go to https://huggingface.co/settings/tokens?new_token=true  
   ![](/blog-img/2024-04-20-llama3/hf-new-token.png)  
    Enter your desired name  
   ![](/blog-img/2024-04-20-llama3/hf-new-token-name.png)  
    Then, keep this token safe (do not share it with anyone)  
   ![](/blog-img/2024-04-20-llama3/hf-token.png)

4. Next, go to the project directory's `kuwa\GenAI OS\windows` folder and execute `tool.bat`
   ![](/blog-img/2024-04-20-llama3/win-hf-login-1.png)  
    Enter the HF Token you just generated, and you can use the mouse right-click to paste it, but this input is invisible, so enter it and press Enter  
   ![](/blog-img/2024-04-20-llama3/win-hf-login-2.png)  
    Enter `n` for the Git certificate part  
   ![](/blog-img/2024-04-20-llama3/win-hf-login-3.png)  
    After that, you will see "Login successful" to indicate that the setting is successful.

### Method 2: Direct Download from HuggingFace without Login

- If you don't want to log in to HuggingFace, you can find a third-party re-uploaded model (named Meta-Llama-3-8B-Instruct, without GGUF):  
    Search on HuggingFace: https://huggingface.co/models?search=Meta-Llama-3-8B-Instruct  
    For example, `NousResearch/Meta-Llama-3-8B-Instruct`, remember the name  
   ![](/blog-img/2024-04-20-llama3/hf-nousresearch.png)

## Setting up Kuwa

1. Go to the `kuwa\GenAI OS\windows\executors` folder, which should have a `huggingface` subfolder by default, enter it, and open `init.bat`  
   ![](/blog-img/2024-04-20-llama3/win-kuwa-init-1.png)  
    You need to enter the model path, which can be the location on HuggingFace, such as:  
    Method 1: `meta-llama/Meta-Llama-3-8B-Instruct`  
    Method 2: `NousResearch/Meta-Llama-3-8B-Instruct`  
   ![](/blog-img/2024-04-20-llama3/win-kuwa-init-2.png)  
    - The image part will automatically find the image in the folder
    - Arguments to use: `"--no_system_prompt" "--stop" "<|eot_id|>"`; if you want to customize parameters, please refer to the README file in the `executor` folder
    - This will automatically create a `run.bat` file in the `kuwa\GenAI OS\windows\executors\huggingface` folder

2. Go back to the project directory's `kuwa\GenAI OS\windows` folder and execute `start.bat` to automatically download and start the model.
3. Note: The downloaded models will be stored in the `.cache\huggingface\hub` folder in the user directory, and if the space is insufficient, please clean up the model cache.

## Using Kuwa

1. Wait for the model to download and then log in to Kuwa to start chatting with Llama3
2. Llama3 is set to prefer English, and you can use the "Translate this model's response" function to translate the model's response into Chinese
    ![](/blog-img/2024-04-20-llama3/kuwa-usage-1.png)
    ![](/blog-img/2024-04-20-llama3/kuwa-usage-2.png)
3. You can use the group chat function to compare the responses of Llama3, Llama2, and TAIDE-LX-7B-Chat
    ![](/blog-img/2024-04-20-llama3/kuwa-usage-3.png)