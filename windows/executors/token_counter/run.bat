set EXECUTOR_ACCESS_CODE=tool/token_counter
pushd ..\..\..\src\multi-chat
php artisan model:config "tool/token_counter" "TokenCounter" --image "..\..\windows\executors\token_counter\counter.png"
popd
pushd ..\..\..\src\executor\token_counter
start /b "" "python" "main.py" "--access_code" "tool/token_counter"