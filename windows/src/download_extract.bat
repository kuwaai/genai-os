@echo off

REM Usage: download_extract.bat <url> <check_location> <folder_name> <archive_name>

set "url=%1"
set "check_location=%2"
set "folder_name=%3"
set "archive_name=%4"

if not exist "%check_location%" (
    echo Downloading %url%...
    curl -L -# -o "%archive_name%" %url%

    :: Check if the file is a tar.xz archive
    if "%archive_name:~-7%"==".tar.xz" (
        echo Extracting %archive_name%...
        tar -xf %archive_name% -C "%folder_name%"
    ) else if "%archive_name:~-7%"==".7z.exe" (
        echo Extracting %archive_name%...
        %archive_name% -o "%folder_name%" -y
    ) else (
        echo Extracting %archive_name%...
        powershell Expand-Archive -Path %archive_name% -DestinationPath "%folder_name%"
    )
    
    echo Cleaning up...
    del %archive_name%
    REM Check if the folder is not empty
    FOR /F %%# in ('dir /b "%folder_name%"') DO (
        echo Unzipping successful.
        EXIT /B
    )
    echo Unzipping failed. Cleaning up...
    RD /Q /S "%folder_name%"
    exit /b 0
) else (
    echo Target file already exists, skipping download and extraction.
)
