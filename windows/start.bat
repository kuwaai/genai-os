
cd /d "..\web"

REM Redis workers
start /b "" php artisan queue:work  --verbose --timeout=600
start /b "" php artisan queue:work  --verbose --timeout=600
start /b "" php artisan queue:work  --verbose --timeout=600
start /b "" php artisan queue:work  --verbose --timeout=600
start /b "" php artisan queue:work  --verbose --timeout=600
start /b "" php artisan queue:work  --verbose --timeout=600
start /b "" php artisan queue:work  --verbose --timeout=600
start /b "" php artisan queue:work  --verbose --timeout=600
start /b "" php artisan queue:work  --verbose --timeout=600
start /b "" php artisan queue:work  --verbose --timeout=600
REM Agent
cd /d "..\LLMs\agent"
del records.pickle
start /b python main.py

REM Wait for Agent online
:CHECK_URL
timeout /t 1 >nul
curl -s -o nul http://127.0.0.1:9000
if %errorlevel% neq 0 (
    goto :CHECK_URL
)

cd ..

REM LLMs
start /b b.11.0.0-4bits.py
start /b chatgpt.py
start /b b.11.0.0-llama_cpp_q4_0.py

REM RAG Applications
REM cd RAG
REM start /b win_run_webqa.bat
REM start /b win_run_docqa.bat
REM start /b win_run_govqa.bat
REM start /b win_run_nstc_searchqa.bat
REM tart /b win_run_searchqa.bat

REM Start web
start http://127.0.0.1
