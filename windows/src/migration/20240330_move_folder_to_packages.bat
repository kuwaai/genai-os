REM This migrationn move the old packages to new location
for %%i in (composer.phar nginx-1.24.0 node-v20.11.1-win-x64 php-8.1.27-Win32-vs16-x64 python-3.9.5-embed-amd64 Redis-6.0.20-Windows-x64-msys2 RunHiddenConsole) do (
    if exist %%i (
        move %%i packages
    )
)

exit /b 0