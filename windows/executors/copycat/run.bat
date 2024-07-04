set EXECUTOR_ACCESS_CODE=tool/copycat
pushd ..\..\..\src\multi-chat
php artisan model:config "tool/copycat" "CopyCat" --image "..\..\windows\executors\copycat\copycat.png"
popd
start /b "" "kuwa-executor" "debug" "--access_code" "tool/copycat"