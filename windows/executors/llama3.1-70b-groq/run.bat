pushd ..\..\src
call variables.bat
popd
set EXECUTOR_ACCESS_CODE=llama3.1-70b
pushd ..\..\..\src\multi-chat
php artisan model:config "llama3.1-70b" "Llama3.1 70B (Groq API)" --image "..\..\windows\executors\llama3.1-70b-groq\llama3_1.jpeg"
popd
start /b "" "kuwa-executor" "chatgpt" "--access_code" "llama3.1-70b" "--base_url" "https://api.groq.com/openai/v1/" "--model" "llama-3.1-70b-versatile" "--context_window" "131072" "--use_third_party_api_key"
