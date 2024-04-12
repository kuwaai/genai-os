set EXECUTOR_ACCESS_CODE=chatgpt
pushd ..\..\..\src\multi-chat
php artisan model:config "chatgpt" "ChatGPT" --image "..\..\windows\executors\chatgpt\chatgpt.png"
popd
start /b "" "kuwa-executor" "chatgpt" "--access_code" "chatgpt"
