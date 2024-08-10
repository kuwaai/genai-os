pushd ..\..\src
call variables.bat
popd
set EXECUTOR_ACCESS_CODE=dall-e
pushd ..\..\..\src\multi-chat
php artisan model:config "dall-e" "DALL-E" --image "..\..\windows\executors\dall-e\dall-e.png"
popd
pushd ..\..\..\src\executor\image_generation
start /b "" "python" dall_e.py "--access_code" "dall-e"
popd
