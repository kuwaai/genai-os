REM Loop to wait for commands
@echo off
call src\variables.bat
:loop
set userInput=
set /p userInput=Enter a command (quit, seed, hf login, prune, stop): 

if /I "%userInput%"=="quit" (
	echo Quit.
) else if /I "%userInput%"=="seed" (
    echo Running seed command...
    call src\migration\20240402_seed_admin.bat
    goto loop
) else if /I "%userInput%"=="hf login" (
    echo Running huggingface login command...
    call src\migration\20240403_login_huggingface.bat
    goto loop
) else if /I "%userInput%"=="stop" (
    echo Stopping everything
    call src\stop.bat
    goto loop
) else if /I "%userInput%"=="prune" (
    echo Running prune command...
	pushd ..\src\multi-chat\
    call ..\..\windows\packages\%php_folder%\php.exe artisan model:prune
	popd
    goto loop
) else (
    goto loop
)
