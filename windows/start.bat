@echo off
cd "%~dp0"
setlocal enabledelayedexpansion

REM Include variables from separate file
call src\variables.bat

REM Redis Server
pushd packages\%redis_folder%
del dump.rdb
start /b "" "redis-server.exe" redis.conf
popd

REM Define number of Redis workers
set numWorkers=10

REM Redis workers
for /l %%i in (1,1,%numWorkers%) do (
	echo Started a model worker
    start /b packages\%php_folder%\php.exe ..\src\multi-chat\artisan queue:work --verbose --timeout=6000
)

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
            set "exclude_access_codes=--exclude=!EXECUTOR_ACCESS_CODE!"
        ) else (
            set "exclude_access_codes=!exclude_access_codes! --exclude=!EXECUTOR_ACCESS_CODE!"
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

REM Start web
start http://127.0.0.1

REM Start Nginx and PHP-FPM
pushd packages\%php_folder%
set PHP_FCGI_MAX_REQUESTS=0
set PHP_FCGI_CHILDREN=20
start /b RunHiddenConsole.exe php-cgi.exe -b 127.0.0.1:9123
popd
pushd "packages\%nginx_folder%"
echo "Nginx started!"
start /b .\nginx.exe
popd

REM Loop to wait for commands
:loop
set userInput=
set /p userInput=Enter a command (stop, seed, hf login): 

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
) else (
    goto loop
)

endlocal