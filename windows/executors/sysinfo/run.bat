set EXECUTOR_ACCESS_CODE=tool/sysinfo
pushd ..\..\..\src\multi-chat
php artisan model:config "tool/sysinfo" "System Info" --image "..\..\windows\executors\sysinfo\sysinfo.png"
popd
start /b "" "kuwa-executor" "sysinfo" "--access_code" "tool/sysinfo"