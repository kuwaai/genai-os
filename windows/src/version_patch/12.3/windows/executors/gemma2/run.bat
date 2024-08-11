pushd ..\..\src
call variables.bat
popd
set EXECUTOR_ACCESS_CODE=gemma2-2b-instruct
pushd ..\..\..\src\multi-chat
php artisan model:config "gemma2-2b-instruct" "Gemma2 2B Instruct" --image "..\..\windows\executors\gemma2\gemma.png"
popd
start /b "" "kuwa-executor" "llamacpp" "--access_code" "gemma2-2b-instruct" "--model_path" "gemma-2-2b-it-Q8_0.gguf" "--ngl" "-1" "--repeat_penalty" "1.0"
