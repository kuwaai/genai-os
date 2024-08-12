Hello developers and users,

After receiving feedback from many users since the initial release, we are pleased to announce the stable release of [v0.1.0](https://github.com/kuwaai/genai-os/tree/v0.1.0). In this version, we have made some adjustments to the installation process for the Windows version. We have also simultaneously released a Docker version, allowing users to quickly install and adjust the environment structure as needed. Additionally, we have fixed some minor bugs that were known in previous versions.

<!-- truncate -->

Here are the main updates in this release:

## Windows Portable Version
1. Adjusted the model setup process to allow for easier configuration of multiple models.
2. Fixed various errors that occurred when using MySQL or PostgreSQL.
3. Readme updated for better completeness.

## Docker Version
1. Docker Compose can now be used to start the entire system and multiple Executors with a single command.
2. Stable software stack selected, suitable for direct use in production environments.
3. Modular design allows for the selection of Executor types and quantities to be launched freely.

## Executor
1. Added a command-line interface launcher that can start multiple Executors with one click, allowing common parameters such as Prompt template, System Prompt, and Generation config to be passed in as commands.
2. Supports common on-premises model inference frameworks such as Huggingface Transformers and Llama.cpp.
3. Supports inference services compatible with OpenAI API or Gemini-Pro API, such as vLLM, LiteLLM, etc.
4. Packaged common functions into the Executor framework, such as automatic registration retry, automatic logout, automatic history record pruning, interrupt generation, etc.
5. Packaged the Executor framework into a package for easy extension of Executors.
6. Fixed a bug in the generation error of the llama.cpp executor.
7. Changed the underlying framework to FastAPI to improve efficiency and stability.

## Multi-chat
1. Fixed bug causing website to jump to /stream route.
2. Added default images for models.
3. Fixed some minor bugs.
4. Added more command-line tools for configuring the website.

For migration from older versions to the new version, please refer to this [migration guide](./migrate-to-kuwa-os-v0.1.0.md).