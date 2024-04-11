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
set PYTHONPATH=%PYTHONPATH%;%~dp0..\src\kernel\src
set "PATH=%~dp0packages\%python_folder%\Scripts;%~dp0packages\%python_folder%\;%PATH%"
start /b "" "%~dp0packages\%python_folder%\python.exe" "%~dp0..\src\kernel\main.py"
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
    rem Check if the env.bat file exists in the current loop folder
    if exist "%%d\env.bat" (
        rem Execute the env.bat file
        call %%d\env.bat

        rem Perform different actions based on the executor type
        rem Use if statements to handle different executor types
        if "!EXECUTOR_TYPE!"=="chatgpt" (
            set "do_extra_action=1"
        ) else if "!EXECUTOR_TYPE!"=="geminipro" (
            set "do_extra_action=1"
        ) else if "!EXECUTOR_TYPE!"=="custom" (
            set "do_extra_action=2"
        ) else (
            set "do_extra_action=0"
        )
        rem Perform extra action if needed
        if "!do_extra_action!"=="1" (
            if defined api_key (
                start /b "" "kuwa-executor" "!EXECUTOR_TYPE!" "--access_code" "!EXECUTOR_ACCESS_CODE!" "--api_key" "!api_key!"
            ) else (
                start /b "" "kuwa-executor" "!EXECUTOR_TYPE!" "--access_code" "!EXECUTOR_ACCESS_CODE!"
            )
        ) else if "!do_extra_action!"=="2" (
            start /b "" "%~dp0packages\%python_folder%\python.exe" !worker_path! "--access_code" "!EXECUTOR_ACCESS_CODE!"
        ) else (
            start /b "" "kuwa-executor" "!EXECUTOR_TYPE!" "--access_code" "!EXECUTOR_ACCESS_CODE!" "--model_path" "!model_path!"
        )

        pushd ..\src\multi-chat\
        if "!image_path!"=="" (
			call ..\..\windows\packages\!php_folder!\php.exe artisan model:config "!EXECUTOR_ACCESS_CODE!" "!EXECUTOR_NAME!"
		) else (
			call ..\..\windows\packages\!php_folder!\php.exe artisan model:config "!EXECUTOR_ACCESS_CODE!" "!EXECUTOR_NAME!" --image "!image_path!"
		)
        popd

        rem Collect existing access code
        if "!exclude_access_codes!"=="" (
            set "exclude_access_codes=--exclude=!EXECUTOR_ACCESS_CODE!"
        ) else (
            set "exclude_access_codes=!exclude_access_codes! --exclude=!EXECUTOR_ACCESS_CODE!"
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

REM Loop to wait for commands
:loop
set userInput=
set /p userInput=Enter a command (stop, seed, hf login): 

if /I "%userInput%"=="stop" (
    echo Stopping Nginx...
    call stop.bat
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

call src\stop.bat
endlocal