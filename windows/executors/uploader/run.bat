call ..\..\src\variables.bat
set EXECUTOR_ACCESS_CODE=uploader
pushd ..\..\..\src\multi-chat
php artisan model:config "uploader" "Uploader" --image "..\..\windows\executors\uploader\upload.png"
popd
pushd ..\..\..\src\executor\uploader
start /b "" "python" main.py "--access_code" "uploader"
popd
