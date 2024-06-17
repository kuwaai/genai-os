set EXECUTOR_ACCESS_CODE=painter
pushd ..\..\..\src\multi-chat
php artisan model:config "painter" "Painter" --image "..\..\windows\executors\painter\painter.png"
popd
start /b "" "python" ..\..\..\src\executor\image_generation\main.py "--access_code" "painter"
