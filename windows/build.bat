@echo off

REM Include variables from separate file
call variables.bat

REM Download and extract RunHiddenConsole if not exists
call download_extract.bat %url_RunHiddenConsole% %RunHiddenConsole_folder% %RunHiddenConsole_folder% RunHiddenConsole.zip

REM Download and extract Node.js if not exists
call download_extract.bat %url_NodeJS% %node_folder% . node.zip

REM Download and extract PHP if not exists
call download_extract.bat %url_PHP% %php_folder% %php_folder% php.zip

REM Download and extract Python if not exists
call download_extract.bat %url_Python% %python_folder% %python_folder% python.zip

REM Download and extract Redis if not exists
call download_extract.bat %url_Redis% %redis_folder% . redis.zip

REM Download and extract CMake if not exists
call download_extract.bat %url_CMake% %cmake_folder% . cmake.zip

REM Copy php.ini if not exists
if not exist "%php_folder%\php.ini" (
    copy ..\multi-chat\php.ini "%php_folder%\php.ini"
) else (
    echo PHP.ini already exists, skipping copy and pasting.
)

REM Copy php_redis.dll if not exists
if not exist "%php_folder%\ext\php_redis.dll" (
    copy php_redis.dll "%php_folder%\ext\php_redis.dll"
) else (
    echo php_redis.dll already exists, skipping copy and pasting.
)


REM Download composer.phar if not exists
if not exist "composer.phar" (
    curl -o composer.phar https://getcomposer.org/download/latest-stable/composer.phar
) else (
    echo Composer already exists, skipping download.
)

REM Prepare RunHiddenConsole.exe if not exists
if not exist "%php_folder%\RunHiddenConsole.exe" (
    copy %RunHiddenConsole_folder%\x64\RunHiddenConsole.exe %php_folder%\
) else (
    echo RunHiddenConsole.exe already exists, skipping copy.
)

REM Prepare get-pip.py
if not exist "%python_folder%\get-pip.py" (
	curl -o "%python_folder%\get-pip.py" https://bootstrap.pypa.io/get-pip.py
) else (
    echo get-pip.py already exists, skipping download.
)

REM Install pip for python
if not exist "%python_folder%\Scripts\pip.exe" (
	pushd "%python_folder%"
	.\python.exe get-pip.py --no-warn-script-location
	popd
) else (
    echo pip already installed, skipping installing.
)

REM Overwrite the python39._pth file
echo Overwrite the python39._pth file.
copy /Y python39._pth "%python_folder%\python39._pth"
set "CMAKE_C_COMPILER=%~dp0%cmake_folder%"
set "CMAKE_CXX_COMPILER=%~dp0%cmake_folder%"
REM Download required pip packages
pushd "%python_folder%"
.\python.exe -m pip install --global-option="--make-spec=mingw" -r ..\..\kernel\requirements.txt
.\python.exe -m pip install --global-option="--make-spec=mingw" -r ..\..\executor\requirements1.txt
.\python.exe -m pip install --global-option="--make-spec=mingw" -r ..\..\executor\requirements2.txt
popd

REM Check if .env file exists
if not exist "..\multi-chat\.env" (
    REM Kuwa Chat
    echo Preparing Kuwa Chat
    copy ..\multi-chat\.env.dev ..\multi-chat\.env
) else (
    echo .env file already exists, skipping copy.
)


set "PATH=%~dp0%node_folder%;%PATH%"

REM Production update
pushd "..\multi-chat"
call ..\windows\%php_folder%\php.exe ..\windows\composer.phar update
call ..\windows\%php_folder%\php.exe artisan key:generate --force
call ..\windows\%php_folder%\php.exe artisan migrate --force
call rmdir public\storage
call ..\windows\%php_folder%\php.exe artisan storage:link
call ..\windows\%node_folder%\npm.cmd install
call ..\windows\%php_folder%\php.exe ..\windows\composer.phar dump-autoload --optimize
call ..\windows\%php_folder%\php.exe artisan route:cache
call ..\windows\%php_folder%\php.exe artisan view:cache
call ..\windows\%php_folder%\php.exe artisan optimize
call ..\windows\%node_folder%\npm.cmd run build
call ..\windows\%php_folder%\php.exe artisan config:cache
call ..\windows\%php_folder%\php.exe artisan config:clear
popd

REM Download and extract Nginx if not exists
call download_extract.bat %url_Nginx% %nginx_folder% . nginx.zip

REM Overwrite the nginx.conf file
echo Overwrite the nginx.conf file.
copy /Y nginx.conf "%nginx_folder%\conf\nginx.conf"

REM Remove folder nginx_folder/html
echo Removing folder %nginx_folder%/html...
rd /s /q "%nginx_folder%\html"

REM Make shortcut from nginx_folder/html to ../public
echo Creating shortcut from %nginx_folder%/html to ../public...
mklink /j "%nginx_folder%\html" "..\multi-chat\public"