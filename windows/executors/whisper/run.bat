set EXECUTOR_ACCESS_CODE=whisper
set "PATH=%~dp0..\..\packages\ffmpeg-7.0.1-essentials_build\bin;%PATH%"
echo %PATH%
pushd ..\..\..\src\multi-chat
php artisan model:config "whisper" "Whisper" --image "..\..\windows\executors\whisper\whisper.png"
popd
start /b "" "python" ..\..\..\src\executor\speech_recognition\main.py "--access_code" "whisper"
