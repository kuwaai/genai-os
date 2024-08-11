:: Import bots
@echo off

setlocal enabledelayedexpansion

pushd "%~dp0..\..\src\multi-chat"
for %%a in ("%KUWA_ROOT%\bootstrap\bot\*.*") do (
    start /b php artisan bot:import "%%~fa"
)
popd
endlocal
exit