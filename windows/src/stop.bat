pushd "packages\%nginx_folder%"
.\nginx.exe -s quit
popd
REM Stop Redis server gracefully
pushd "packages\%redis_folder%"
redis-cli.exe shutdown
popd
REM Cleanup everything
pushd "..\src\multi-chat\"
call php artisan worker:stop
popd
taskkill /F /IM "nginx.exe"
taskkill /F /IM "redis-server.exe"
taskkill /F /IM "php-cgi.exe"
taskkill /F /IM "php.exe"
taskkill /F /IM "python.exe"