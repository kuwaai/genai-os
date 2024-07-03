set EXECUTOR_ACCESS_CODE=sysinfo
pushd ..\..\..\src\multi-chat
php artisan model:config "sysinfo" "System Info" --image "..\..\windows\executors\sysinfo\sysinfo.png"
popd
start /b "" "kuwa-executor" "sysinfo" "--access_code" "sysinfo"