@echo off

REM Inits
set "url=https://nginx.org/download/nginx-1.24.0.zip"
for %%I in ("%url%") do set "filename=%%~nxI"

set "zipfile=%filename:~0,-4%"
for /f "tokens=2 delims=-" %%v in ("%filename%") do set "version=%%v"

REM Check if nginx.exe exists
if not exist "%zipfile%\nginx.exe" (
    echo Preparing Nginx
    echo Downloading %url%...
    curl -L -o %filename% %url%
    echo Extracting %filename%...
    powershell Expand-Archive -Path %filename% -DestinationPath .
    echo Cleaning up...
    del %filename%
) else (
    echo Nginx already exists, skipping download and extraction.
)

REM Check if .env file exists
if not exist "..\web\.env" (
    REM Kuwa Chat
    echo Preparing Kuwa Chat
    copy .env ..\web\
) else (
    echo .env file already exists, skipping copy.
)

Rem update
cd /d ..\web\executables\bat
call .\production_update.bat
cd /d ..\windows

REM Remove folder zipfile/html
echo Removing folder %zipfile%/html...
rd /s /q "%zipfile%\html"

REM Make shortcut from zipfile/html to ../web/public
echo Creating shortcut from %zipfile%/html to ../web/public...
mklink /j "%zipfile%\html" "..\web\public"

REM Start Nginx
pushd "%zipfile%"
echo "Nginx started!"
start /b .\nginx.exe

REM Trap any key press to stop Nginx
echo Press any key to stop Nginx...
pause > nul
.\nginx.exe -s quit
echo Nginx stopped
popd
