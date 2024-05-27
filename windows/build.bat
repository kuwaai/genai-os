@echo off
cd "%~dp0"
if not defined in_subprocess (cmd /k set in_subprocess=y ^& %0 %* 2^>^&1 ^| src\tee.bat logs\build.log ^& exit /b & exit /b)

REM Initialize everything
call src\variables.bat

REM Download and extract RunHiddenConsole if not exists
call src\download_extract.bat %url_RunHiddenConsole% packages\%RunHiddenConsole_folder% packages\%RunHiddenConsole_folder% RunHiddenConsole.zip

REM Download and extract Node.js if not exists
call src\download_extract.bat %url_NodeJS% packages\%node_folder% packages\. node.zip

REM Download and extract PHP if not exists
call src\download_extract.bat %url_PHP% packages\%php_folder% packages\%php_folder% php.zip

REM Download and extract PHP if not exists
call src\download_extract.bat %url_PHP_Archive% packages\%php_folder_Archive% packages\%php_folder_Archive% php.zip

REM Download and extract xpdfreader if not exists
call src\download_extract.bat %url_XpdfReader% packages\%xpdfreader_folder% packages\. xpdfreader.zip

REM Download and extract antiword if not exists
call src\download_extract.bat %url_antiword% packages\%antiword_folder% packages\. antiword.zip

REM Download and extract git bash if not exists
git --version >nul 2>&1
if %ERRORLEVEL% equ 0 (
    echo Git is installed, skip downloading git bash
) else (
	call src\download_extract.bat %url_gitbash% packages\%gitbash_folder% packages\%gitbash_folder% gitbash.7z.exe
    echo Git is not installed.
)

IF EXIST packages\%python_folder% (
    echo Python folder already exists.
) ELSE (
    REM Download and extract Python if not exists
    call src\download_extract.bat %url_Python% packages\%python_folder% packages\%python_folder% python.zip
    REM Overwrite the python310._pth file
    echo Overwrite the python310._pth file.
    copy /Y src\python310._pth "packages\%python_folder%\python310._pth"
)

REM Download and extract Redis if not exists
call src\download_extract.bat %url_Redis% packages\%redis_folder% packages\. redis.zip

IF EXIST packages\%nginx_folder% (
    echo Nginx folder already exists.
) ELSE (
    REM Download and extract Nginx if not exists
    call src\download_extract.bat %url_Nginx% packages\%nginx_folder% packages\. nginx.zip
    REM Overwrite the nginx.conf file
    echo Overwrite the nginx.conf file.
    copy /Y src\nginx.conf "packages\%nginx_folder%\conf\nginx.conf"
)

REM Copy php.ini if not exists
if not exist "packages\%php_folder%\php.ini" (
    copy ..\src\multi-chat\php.ini "packages\%php_folder%\php.ini"
) else (
    echo php.ini already exists, skipping copy and pasting.
)

REM Copy php_redis.dll if not exists
if not exist "packages\%php_folder%\ext\php_redis.dll" (
    copy src\php_redis.dll "packages\%php_folder%\ext\php_redis.dll"
) else (
    echo php_redis.dll already exists, skipping copy and pasting.
)

REM Download composer.phar if not exists
if not exist "packages\composer.phar" (
    curl -o packages\composer.phar https://getcomposer.org/download/latest-stable/composer.phar
) else (
    echo Composer already exists, skipping download.
)

REM Prepare RunHiddenConsole.exe if not exists
if not exist "packages\%php_folder%\RunHiddenConsole.exe" (
    copy packages\%RunHiddenConsole_folder%\x64\RunHiddenConsole.exe packages\%php_folder%\
) else (
    echo RunHiddenConsole.exe already exists, skipping copy.
)

REM Prepare get-pip.py
if not exist "packages\%python_folder%\get-pip.py" (
	curl -o "packages\%python_folder%\get-pip.py" https://bootstrap.pypa.io/get-pip.py
) else (
    echo get-pip.py already exists, skipping download.
)

REM Install pip for python
if not exist "packages\%python_folder%\Scripts\pip.exe" (
	pushd "packages\%python_folder%"
	python get-pip.py --no-warn-script-location
	popd
) else (
    echo pip already installed, skipping installing.
)

REM Check if .env file exists
if not exist "..\src\multi-chat\.env" (
    REM Kuwa Chat
    echo Preparing Kuwa Chat
    copy ..\src\multi-chat\.env.dev ..\src\multi-chat\.env
) else (
    echo .env file already exists, skipping copy.
)

set "PATH=%~dp0packages\%node_folder%;%PATH%"

REM Production update
pushd "..\src\multi-chat"
call php ..\..\windows\packages\composer.phar update
call php artisan key:generate --force
call php artisan db:seed --class=InitSeeder --force
call php artisan migrate --force
rmdir /Q /S public\storage
call php artisan storage:link
call npm.cmd install
call php ..\..\windows\packages\composer.phar dump-autoload --optimize
call php artisan route:cache
call php artisan view:cache
call php artisan optimize
call npm.cmd run build
call php artisan config:cache
call php artisan config:clear
popd

REM Remove folder nginx_folder/html
echo Removing folder %nginx_folder%/html...
rmdir /Q /S "packages\%nginx_folder%\html"

REM Make shortcut from nginx_folder/html to ../public
echo Creating shortcut from %nginx_folder%/html to ../public...
mklink /j "%~dp0packages\%nginx_folder%\html" "%~dp0..\src\multi-chat\public"

REM Download required pip packages
pip install -r .\src\requirements.txt
pushd "..\src\kernel"
pip install -r requirements.txt 
popd
pushd "..\src\executor"
pip install -r requirements.txt
pushd "docqa"
pip install -r requirements.txt
popd
popd
pushd "..\src\toolchain"
pip install -r requirements.txt 
popd