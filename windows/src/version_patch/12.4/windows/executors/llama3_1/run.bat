pushd ..\..\src
call variables.bat
popd
set EXECUTOR_ACCESS_CODE=llama3.1-8b-instruct
pushd ..\..\..\src\multi-chat
php artisan model:config "llama3.1-8b-instruct" "LLaMA3.1 8B Instruct" --image "..\..\windows\executors\llama3_1\llama3_1.jpeg"
popd
start /b "" "kuwa-executor" "llamacpp" "--access_code" "llama3.1-8b-instruct" "--model_path" "llama3_1-8b-q4_k_m.gguf" "--ngl" "-1" "--stop" "<|eot_id|>"
