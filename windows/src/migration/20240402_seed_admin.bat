REM This help user quickly setup their own account

set php_exe=packages\%php_folder%\php.exe

if exist "%php_exe%" (
    %php_exe% ..\src\multi-chat\artisan db:seed --class=AdminSeeder --force
    exit /b 0
) else (
    echo "PHP executable not found!"
    exit /b 1
)
