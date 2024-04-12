set EXECUTOR_ACCESS_CODE=geminipro
pushd ..\..\..\src\multi-chat
php artisan model:config "geminipro" "Gemini Pro" --image "..\..\windows\executors\geminipro\geminipro.png"
popd
start /b "" "kuwa-executor" "geminipro" "--access_code" "geminipro"
