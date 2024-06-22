@echo off
cd "%~dp0"
setlocal enabledelayedexpansion

REM Include variables from separate file
call src\variables.bat
cd "%~dp0"

REM Check for updates
git fetch
git status -uno | findstr "behind"
IF %ERRORLEVEL% EQU 0 (
    REM Prompt the user for input with default 'Y'
    set "response=Y"
    set /p "response=Do you want to update? (Y/N) [Y]: "
    
    REM If the user just presses Enter, set response to 'Y'
    if "!response!"=="" set response=Y
    
    REM Convert input to uppercase
    set "response=!response:~0,1!"
    
    REM Check user response
    if /I "!response!"=="Y" (
        git stash
        git pull
        call build.bat
    ) else (
        echo Update skipped.
    )
) ELSE (
    echo Your branch is up to date.
)
pause