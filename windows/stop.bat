@echo off

REM Include variables from separate file
call variables.bat
pushd "%nginx_folder%"
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
taskkill /F /IM "redis-server.exe"