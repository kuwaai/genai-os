This post will guide you to upgrade Kuwa TAIDE’s built-in model from TAIDE-LX-7B-Chat-4bit to Llama3-TAIDE-LX-8B-Chat-Alpha1-4bit.

1. Go to `C:\kuwa\GenAI OS\windows\executors`, and duplicate the `1_taide` directory to `1_taide-8b`. If you only need to run the new version of TAIDE model, you can delete the `run.bat` file in `1_taide`.
    ![](./img/2024-04-29-taide-8b/copy-taide-config.png)

2. Download `taide-8b-a.3-q4_k_m.gguf` from the official [TAIDE HuggingFace Hub](https://huggingface.co/taide/Llama3-TAIDE-LX-8B-Chat-Alpha1-4bit) to `C:\kuwa\GenAI OS\windows\executors\1-taide_8b`, and delete the original `taide-7b-a.2-q4_k_m.gguf` 
    ![](./img/2024-04-29-taide-8b/download-model.png)

<!-- truncate -->

3. Run init.bat, and use the following settings:
    - Enter the option number (1-5): `3`
    - Enter the model name: `Llama3 TAIDE LX 8B Chat Alpha1 4bit`
    - Enter the access code: `taide-8b`
    - Arguments to use (...): `--stop "<|eot_id|>"`
    ![](./img/2024-04-29-taide-8b/config-model.png)

4. Restart Kuwa GenAI OS and you should see the new version of TAIDE model
    ![](./img/2024-04-29-taide-8b/new-model-added.png)

5. You can use the multi-chat feature to compare the responses from two TAIDE models at the same time
    ![](./img/2024-04-29-taide-8b/multi-chat-result.png)