
@echo off

echo Preparing Nginx
set "url=https://nginx.org/download/nginx-1.24.0.zip"
set "zipfile=nginx.zip"
set "extractdir=nginx"
echo Downloading %url%...
curl -L -o %zipfile% %url%
echo Extracting %zipfile%...
powershell Expand-Archive -Path %zipfile% -DestinationPath %extractdir%
echo Cleaning up...
del %zipfile%

echo Preparing Kuwa Chat
copy .env ..\web\
..\web\executables\bat\production_update.bat