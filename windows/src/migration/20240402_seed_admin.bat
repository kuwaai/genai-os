REM This help user quickly setup their own account

call packages\%php_folder%\php.exe ..\src\multi-chat\artisan db:seed --class=AdminSeeder --force

exit /b 0