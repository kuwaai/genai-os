@echo off

REM Include variables from separate file
call variables.bat

REM Production update
pushd "..\multi-chat"
call ..\windows\%php_folder%\php.exe artisan db:seed --class=AdminSeeder --force
popd