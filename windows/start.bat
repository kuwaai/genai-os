@echo off
setlocal enabledelayedexpansion

REM Include variables from separate file
call src\variables.bat

REM Start Kuwa workers

REM Redis Server

pushd packages\%redis_folder%
del dump.rdb
start /b "" "redis-server.exe" redis.conf
popd

REM Define number of workers
set numWorkers=10

REM Redis workers
for /l %%i in (1,1,%numWorkers%) do (
	echo Started a model worker
    start /b packages\%php_folder%\php.exe ..\src\multi-chat\artisan queue:work --verbose --timeout=6000
)

REM Agent
pushd "..\src\kernel"
del records.pickle
set PYTHONPATH=%PYTHONPATH%;%~dp0..\src\kernel\src
set "PATH=%~dp0packages\%python_folder%\Scripts;%PATH%"
start /b "" "%~dp0packages\%python_folder%\python.exe" "%~dp0..\src\kernel\main.py"
popd

REM Wait for Agent online
:CHECK_URL
timeout /t 1 >nul
curl -s -o nul http://127.0.0.1:9000
if %errorlevel% neq 0 (
    goto :CHECK_URL
)

REM Prepare workers and collect existing access codes
set "exclude_access_codes="
for /D %%d in ("workers\*") do (
    rem Check if the env.bat file exists in the current loop folder
    if exist "%%d\env.bat" (
        rem Execute the env.bat file
        call %%d\env.bat
        set "input=%%d"
        for /f "tokens=2 delims=\" %%e in ("!input!") do set "current_folder=%%e"

        rem Perform different actions based on the model type
        rem Use if statements to handle different model types
        if "!model_type!"=="chatgpt" (
            set "do_extra_action=1"
        ) else if "!model_type!"=="geminipro" (
            set "do_extra_action=1"
        ) else (
            set "do_extra_action=0"
        )

        rem Perform extra action if needed
        if "!do_extra_action!"=="1" (
            if defined api_key (
                start /b "" "kuwa-executor" "!model_type!" "--access_code" "!current_folder!" "--api_key" "!api_key!"
            ) else (
                start /b "" "kuwa-executor" "!model_type!" "--access_code" "!current_folder!"
            )
        ) else (
            start /b "" "kuwa-executor" "!model_type!" "--access_code" "!current_folder!" "--model_path" "!model_path!"
        )

        pushd ..\src\multi-chat\
        call ..\..\windows\packages\!php_folder!\php.exe artisan model:config "!current_folder!" "!model_name!"
        popd

        rem Collect existing access code
        if "!exclude_access_codes!"=="" (
            set "exclude_access_codes=--exclude=!current_folder!"
        ) else (
            set "exclude_access_codes=!exclude_access_codes! --exclude=!current_folder!"
        )
    )
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

REM Loop to wait for "stop" command
:loop
set /p userInput=Type "stop" to stop the server:
set userInput=%userInput:~0,4%
if /I "%userInput%"=="stop" (
    echo Stopping Nginx...
    call stop.bat
) else (
    goto loop
)

call stop.bat
endlocal