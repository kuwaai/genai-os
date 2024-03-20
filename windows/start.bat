@echo off

REM Include variables from separate file
call variables.bat

REM Start Kuwa workers
REM Define number of workers
set numWorkers=10
REM Redis workers
for /l %%i in (1,1,%numWorkers%) do (
	echo Started a model worker
    start /b %php_folder%\php.exe ..\multi-chat\web\artisan queue:work --verbose --timeout=600
)

REM Agent
pushd "..\multi-chat\LLMs\agent"
del records.pickle
set PYTHONPATH=%PYTHONPATH%;%~dp0..\multi-chat\LLMs\agent\src
start /b %~dp0%python_folder%\python.exe %~dp0..\multi-chat\LLMs\agent\main.py
popd


REM Wait for Agent online
:CHECK_URL
timeout /t 1 >nul
curl -s -o nul http://127.0.0.1:9000
if %errorlevel% neq 0 (
    goto :CHECK_URL
)
REM LLMs
REM start /b b.11.0.0-4bits.py
REM start /b chatgpt.py
REM start /b b.11.0.0-llama_cpp_q4_0.py

REM RAG Applications
REM cd RAG
REM start /b win_run_webqa.bat
REM start /b win_run_docqa.bat
REM start /b win_run_govqa.bat
REM start /b win_run_nstc_searchqa.bat
REM tart /b win_run_searchqa.bat

REM Start web
start http://127.0.0.1

REM Start Nginx and PHP-FPM
pushd %php_folder%
set PHP_FCGI_MAX_REQUESTS=0
set PHP_FCGI_CHILDREN=20
start /b RunHiddenConsole.exe php-cgi.exe -b 127.0.0.1:9123
popd
pushd "%nginx_folder%"
echo "Nginx started!"
start /b .\nginx.exe

REM Trap any key press to stop Nginx
echo Press any key to stop Nginx...
pause > nul
.\nginx.exe -s quit
echo Nginx stopped
popd
REM Stop PHP-FPM gracefully
echo "Stopping PHP-FPM..."
echo PHP-FPM stopped
taskkill /F /IM "nginx.exe"
taskkill /F /IM "php-cgi.exe"
taskkill /F /IM "php.exe"
taskkill /F /IM "python.exe"