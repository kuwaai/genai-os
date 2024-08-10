pushd ..\..\src
call variables.bat
popd
set EXECUTOR_ACCESS_CODE=pipe
pushd ..\..\..\src\multi-chat
php artisan model:config "pipe" "Pipe" --image "..\..\windows\executors\pipe\pipe.png"
popd
pushd ..\..\..\src\executor\pipe
start /b "" "python" main.py "--access_code" "pipe" --log debug
popd