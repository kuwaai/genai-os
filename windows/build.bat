@echo off

REM Inits
set "url=https://github.com/wenshui2008/RunHiddenConsole/releases/download/1.0/RunHiddenConsole.zip"
for %%I in ("%url%") do set "filename=%%~nxI"

set "RunHiddenConsole_folder=%filename:~0,-4%"

REM Check if the target file exists
if not exist "%RunHiddenConsole_folder%" (
    echo Downloading %url%...
    curl -L -o %filename% %url%
    echo Extracting %filename%...
    powershell Expand-Archive -Path %filename% -DestinationPath "%RunHiddenConsole_folder%"
    echo Cleaning up...
    del %filename%
	copy %RunHiddenConsole_folder%\x64\RunHiddenConsole.exe %php_folder%\
) else (
    echo Target file already exists, skipping download and extraction.
)

REM Inits
set "url=https://nodejs.org/dist/v20.11.1/node-v20.11.1-win-x64.zip"
for %%I in ("%url%") do set "filename=%%~nxI"

set "node_folder=%filename:~0,-4%"
for /f "tokens=2 delims=-" %%v in ("%filename%") do set "version=%%v"

REM Check if node.exe exists
if not exist "%node_folder%\node.exe" (
    echo Preparing Node.js
    echo Downloading %url%...
    curl -L -o %filename% %url%
    echo Extracting %filename%...
    powershell Expand-Archive -Path %filename% -DestinationPath .
    echo Cleaning up...
    del %filename%
) else (
    echo Node.js already exists, skipping download and extraction.
)

REM Inits
set "url=https://windows.php.net/downloads/releases/php-8.1.27-Win32-vs16-x64.zip"
for %%I in ("%url%") do set "filename=%%~nxI"

set "php_folder=%filename:~0,-4%"
for /f "tokens=2 delims=-" %%v in ("%filename%") do set "version=%%v"

REM Check if php.exe exists
if not exist "%php_folder%\php.exe" (
    echo Preparing PHP
    echo Downloading %url%...
    curl -L -o %filename% %url%
    echo Extracting %filename%...
    powershell Expand-Archive -Path %filename% -DestinationPath "%php_folder%"
    echo Cleaning up...
    del %filename%
) else (
    echo PHP already exists, skipping download and extraction.
)

if not exist "%php_folder%\php.ini" (
	copy php.ini "%php_folder%\php.ini"
) else (
    echo PHP.ini already exists, skipping copy and pasting.
)

if not exist "composer.phar" (
	curl -o composer.phar https://getcomposer.org/download/latest-stable/composer.phar
) else (
    echo Composer already exists, skipping download.
)
REM Production update
pushd "..\web"
call ..\windows\%php_folder%\php.exe ..\windows\composer.phar update
call ..\windows\%php_folder%\php.exe artisan key:generate --force
call ..\windows\%php_folder%\php.exe artisan migrate --force
call rmdir public\storage
call ..\windows\%php_folder%\php.exe artisan storage:link
call ..\windows\%node_folder%\node.exe ..\windows\%node_folder%\node_modules\npm\bin\npm-cli.js install
call ..\windows\%php_folder%\php.exe ..\windows\composer.phar dump-autoload --optimize
call ..\windows\%php_folder%\php.exe artisan route:cache
call ..\windows\%php_folder%\php.exe artisan view:cache
call ..\windows\%php_folder%\php.exe artisan optimize
call ..\windows\%node_folder%\node.exe ..\windows\%node_folder%\node_modules\npm\bin\npm-cli.js run build
call ..\windows\%php_folder%\php.exe artisan config:cache
call ..\windows\%php_folder%\php.exe artisan config:clear
popd

REM Inits
set "url=https://nginx.org/download/nginx-1.24.0.zip"
for %%I in ("%url%") do set "filename=%%~nxI"

set "nginx_folder=%filename:~0,-4%"
for /f "tokens=2 delims=-" %%v in ("%filename%") do set "version=%%v"

REM Check if nginx.exe exists
if not exist "%nginx_folder%\nginx.exe" (
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
REM Copy nginx.conf file to nginx_folder\conf\nginx.conf
echo Overwrite the nginx.conf file.
copy /Y nginx.conf "%nginx_folder%\conf\nginx.conf"

REM Check if .env file exists
if not exist "..\web\.env" (
    REM Kuwa Chat
    echo Preparing Kuwa Chat
    copy .env ..\web\
) else (
    echo .env file already exists, skipping copy.
)

REM Remove folder nginx_folder/html
echo Removing folder %nginx_folder%/html...
rd /s /q "%nginx_folder%\html"

REM Make shortcut from nginx_folder/html to ../web/public
echo Creating shortcut from %nginx_folder%/html to ../web/public...
mklink /j "%nginx_folder%\html" "..\web\public"

REM Start Nginx
pushd "%nginx_folder%"
echo "Nginx started!"
start /b .\nginx.exe
pushd ..\%php_folder%
RunHiddenConsole.exe php-cgi.exe -b 127.0.0.1:9123
popd

REM Trap any key press to stop Nginx
echo Press any key to stop Nginx...
pause > nul
.\nginx.exe -s quit
echo Nginx stopped
popd
REM Stop PHP-FPM gracefully
echo "Stopping PHP-FPM..."
taskkill /F /IM "%php_folder%\php-fpm.exe"
echo PHP-FPM stopped
