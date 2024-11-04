@echo off
chcp 65001 > NUL
set PYTHONUTF8=1
set PYTHONIOENCODING="utf8"
cd "%~dp0"
if "%1" equ "__start__" (shift & goto main)
if not exist "logs" mkdir logs
cmd /s /c "%0 __start__ %* 2>&1 | src\bin\tee.exe logs\start.log"
exit /b

:main
setlocal enabledelayedexpansion

REM Include variables from separate file
call src\variables.bat
cd "%~dp0"

SET filePath=packages\composer.bat

REM Check if the file exists
IF NOT EXIST "%filePath%" (
    REM Make executable file for Windows
    echo :: in case DelayedExpansion is on and a path contains ! > "%filePath%"
    echo setlocal DISABLEDELAYEDEXPANSION >> "%filePath%"
    echo php "%%~dp0composer.phar" %%* >> "%filePath%"
)

REM Redis Server
pushd packages\%redis_folder%
del dump.rdb
start /b "" "redis-server.exe" redis.conf
popd

pushd "..\src\multi-chat\"
REM Remove web cache
rmdir /S /Q storage\framework\cache
REM Configure PATH for web
start /b php artisan web:config --settings="updateweb_path=%PATH%"
REM Define number of workers
set numWorkers=10
start /b php artisan worker:start %numWorkers%
popd

:launch_kernel_and_executors
REM Kernel
pushd "..\src\kernel"
del records.pickle
start /b "" "kuwa-kernel"
popd

REM Wait for Kernel online
:CHECK_URL
timeout /t 1 >nul
curl -s -o nul http://127.0.0.1:9000
if %errorlevel% neq 0 (
    goto :CHECK_URL
)

REM Prepare executors and collect existing access codes
set "exclude_access_codes="
for /D %%d in ("executors\*") do (
    rem Check if the run.bat file exists in the current loop folder
    pushd %%d
    if exist "init.bat" if not exist "run.bat" (
        call init.bat quick
    )

    if exist "run.bat" (
        rem Execute the run.bat file
        call run.bat

        rem Collect existing access code
        if "!exclude_access_codes!"=="" (
            set "exclude_access_codes=--exclude="!EXECUTOR_ACCESS_CODE!""
        ) else (
            set "exclude_access_codes=!exclude_access_codes! --exclude="!EXECUTOR_ACCESS_CODE!""
        )
    ) 
    popd
)
REM Prune unused access codes
if not "!exclude_access_codes!"=="" (
	pushd ..\src\multi-chat\
	call ..\..\windows\packages\!php_folder!\php.exe artisan model:prune --force !exclude_access_codes!
	popd
)
if defined web_started (
    goto skip_web
)

REM Start web
start http://127.0.0.1
REM Remake public/storage
pushd "%~dp0..\src\multi-chat"
rmdir /Q /S "public\storage"
rmdir /Q /S "storage\app\public\root\custom"
rmdir /Q /S "storage\app\public\root\database"
rmdir /Q /S "storage\app\public\root\bin"
rmdir /Q /S "storage\app\public\root\bot"
call php artisan storage:link
popd

REM Start Nginx and PHP-FPM
pushd packages\%php_folder%
set PHP_FCGI_MAX_REQUESTS=0
set PHP_FCGI_CHILDREN=20
start /b RunHiddenConsole.exe php-cgi.exe -b 127.0.0.1:9123
popd

REM Remove folder nginx_folder/html
echo Removing folder %nginx_folder%/html...
rmdir /Q /S "packages\%nginx_folder%\html"

REM Make shortcut from nginx_folder/html to multi-chat/public
echo Creating shortcut from %nginx_folder%/html to ../public...
mklink /j "packages\%nginx_folder%\html" "%~dp0..\src\multi-chat\public"

pushd "packages\%nginx_folder%"

echo "Nginx started!"
start /b .\nginx.exe
popd
set "web_started=True"

:skip_web

start /b src\import_bots.bat

REM Loop to wait for commands
:loop
set userInput=
set /p userInput=Enter a command (stop, seed, hf login, reload): 

if /I "%userInput%"=="stop" (
    echo Stopping everything...
	call src\stop.bat
) else if /I "%userInput%"=="seed" (
    echo Running seed command...
    call src\migration\20240402_seed_admin.bat
    goto loop
) else if /I "%userInput%"=="hf login" (
    echo Running huggingface login command...
    call src\migration\20240403_login_huggingface.bat
    goto loop
) else if /I "%userInput%"=="reload" (
    echo Reloading kernel and executors...
    taskkill /F /IM "python.exe"
    goto launch_kernel_and_executors
) else (
    goto loop
)

endlocal