@echo off

REM Include variables from separate file
call variables.bat

REM Start Kuwa workers
pushd "..\web"
REM Redis workers
start /b "" ..\windows\%php_folder%\php.exe artisan queue:work  --verbose --timeout=600
start /b "" ..\windows\%php_folder%\php.exe artisan queue:work  --verbose --timeout=600
start /b "" ..\windows\%php_folder%\php.exe artisan queue:work  --verbose --timeout=600
start /b "" ..\windows\%php_folder%\php.exe artisan queue:work  --verbose --timeout=600
start /b "" ..\windows\%php_folder%\php.exe artisan queue:work  --verbose --timeout=600
start /b "" ..\windows\%php_folder%\php.exe artisan queue:work  --verbose --timeout=600
start /b "" ..\windows\%php_folder%\php.exe artisan queue:work  --verbose --timeout=600
start /b "" ..\windows\%php_folder%\php.exe artisan queue:work  --verbose --timeout=600
start /b "" ..\windows\%php_folder%\php.exe artisan queue:work  --verbose --timeout=600
start /b "" ..\windows\%php_folder%\php.exe artisan queue:work  --verbose --timeout=600
popd

REM Agent
pushd "..\LLMs\agent"
del records.pickle
start /b python main.py
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
pushd %php_folder%
taskkill /F /IM "php-cgi.exe"
popd
echo PHP-FPM stopped

pause
exit