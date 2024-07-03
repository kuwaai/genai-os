set EXECUTOR_ACCESS_CODE=copycat
pushd ..\..\..\src\multi-chat
php artisan model:config "copycat" "CopyCat" --image "..\..\windows\executors\copycat\copycat.png"
popd
start /b "" "kuwa-executor" "debug" "--access_code" "copycat"