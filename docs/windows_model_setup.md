## Windows Portable Model Deployment Tutorial
Currently, for simplifying model deployment in Windows, a simple model management system is prepared in the Windows portable version. This management system is only for simple use and testing. It is not recommended to be used in Production scenarios. If you want to use it in Production, please refer to the [tutorial](../src/executor/README.md).

This model deployment tutorial assumes that you have executed `windows/build & start.bat` or `windows/build.bat` + `windows/start.bat`, and the system can log in without a problem. If the above is not your case, please go back to the steps of [this tutorial](./windows_installation.md).

## Introduction
In the `windows/executors` folder, you should see the following folders. You can change the folder name at your will; however, the following five names are reserved by default for easy model setup:
1. chatgpt
2. custom
3. geminipro
4. huggingface
5. llamacpp

Each folder has an init.bat file to configure the run.bat file. You can edit the run.bat file directly or write your own file; however, make sure the parameters and format are correct. After the configuration is complete, restart the start.bat file to take effect.

You can also place images in the folder to automatically put them into the model when the model is first created (if the model has already been created on the website, you need to manually put the images in).

By default, these models will only be available to users with Manage Tab permissions.

## Quick Model Setup Tutorial

### ChatGPT
1. Enter the `chatgpt` folder.
2. Execute the `init.bat` file.
3. Enter the OpenAI API Token. If you do not want to set a global token, you can leave it blank and press Enter to skip.
4. Enter additional parameters (please refer to the [tutorial](../src/executor/README.md) for the kuwa-executor command parameters. If you are not sure what to fill in, leave it blank and press Enter to skip)

### Gemini
1. Enter the `geminipro` folder.
2. Execute the `init.bat` file.
3. Enter the Google API Token. If you do not want to set a global token, you can leave it blank and press Enter to skip.
4. Enter additional parameters (please refer to the [tutorial](../src/executor/README.md) for the kuwa-executor command parameters. If you are not sure what to fill in, leave it blank and press Enter to skip)

### LLaMA.cpp
1. Enter the `llamacpp` folder.
2. Put the .gguf file in the folder.
3. Execute the `init.bat` file.
4. If prompted for a missing .gguf file, enter the absolute path to the file.
5. Enter additional parameters (please refer to the [tutorial](../src/executor/README.md) for the kuwa-executor command parameters. If you are not sure what to fill in, leave it blank and press Enter directly)

### Huggingface
1. Enter the `huggingface` folder.
2. Put the model and tokenizer.
3. Execute the `init.bat` file, and it will automatically detect whether there is a model in the directory. If it is not automatically detected, enter the absolute path to the model folder or the location of the model on huggingface.
4. Enter additional parameters (please refer to the [tutorial](../src/executor/README.md) for the kuwa-executor command parameters. If you are not sure what to fill in, leave it blank and press Enter)

### Custom
- This is a reserved custom model. Users can rewrite a version of the executor (inheriting kuwa LLMWorker), and specify the .py file here for easy execution. The steps are the same as above, but there is an additional worker_path parameter, which needs to be used to point to the absolute path of the .py file.

## Advanced Usage
You can execute multiple models in multiple folders using the same `access_code`. This allows you to handle multiple requests simultaneously. You can also duplicate folders to create more model executions. These models do not have to be on the same host; you can distribute them across multiple hosts. Just set the Kernel endpoint to the kernel. For detailed instructions, please see [here](../src/executor/README.md).

If you are using other TGI frameworks like ollama or vLLM, you can also quickly concatenate using ChatGPT's worker.