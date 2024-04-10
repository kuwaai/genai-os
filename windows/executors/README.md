## Windows Portable Model Deploy Guide
Currently, to simplify model deployment, a simple model management system has been prepared in the Windows portable version. This management system is only for simple use and testing and is not recommended for Production. If you want to use it for Production, please visit this [tutorial](../../src/executor/README.md).

The model deployment tutorial here assumes that you have executed `windows/build.bat` and `windows/start.bat` and that the system can log in without problems. If the above is not the case for you, please go back to the steps in [this tutorial](../README.md).

## Introduce
In the `windows/workers` folder, you should see the following folders. You can change the folder names at will, but the following five names are reserved by default for easy model setup:
1. chatgpt
2. custom
3. geminipro
4. huggingface
5. llamacpp

Each folder has an init.bat that is used to setup the env.bat file. You can edit the env.bat file directly or write your own file, but make sure the parameters and format are correct, and that start.bat is restarted after setup for the changes to take effect.

You can also place images in the folder so that they are automatically included when the model is first created (if the model has already been created on the website, you need to manually include the image)

By default, these models are only available to users with Manage Tab permissions.

## Model Quick Setup Guide

### ChatGPT
1. Enter the `chatgpt` folder.
2. Execute the `init.bat` file.
3. Enter the OpenAI API Token. If you do not want to set a global Token, you can leave it blank and press Enter to skip.

### Gemini Pro
1. Enter the `geminipro` folder.
2. Execute the `init.bat` file.
3. Enter the Google API Token. If you do not want to set a global Token, you can leave it blank and press Enter to skip.

### LLaMA.cpp
1. Enter the `llamacpp` folder.
2. Place the .gguf file in the folder.
3. Execute the `init.bat` file.
4. If the .gguf file cannot be found, please enter the absolute path of the file.

### Huggingface
1. Enter the `huggingface` folder.
2. Place the model and tokenizer.
3. Execute the `init.bat` file, which will automatically detect if the directory contains a model. If it is not detected automatically, please enter the absolute path of the model directory or the model location on huggingface.

### Custom
- This is a reserved custom model, users can rewrite a version of executor (inherit kuwa LLMWorker), and specify the .py file here for easy execution. The steps are the same as above, except that there is an additional worker_path parameter, which needs to point to the .py file using the absolute path.

## Advanced Usage
You can execute multiple models in multiple folders using the same `access_code`. This allows you to handle multiple requests at the same time. You can also copy folders to create more models to execute. These models do not have to be on the same host, you can spread them across multiple hosts, just set the Kernel endpoint to the kernel. For detailed instructions, please refer to [here](../../src/executor/README.md).

If you are using other TGI frameworks such as ollama or vLLM, you can also quickly connect them using ChatGPT's worker.