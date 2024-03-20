@echo off

REM Usage: download_extract.bat <url> <folder_name> <archive_name>

set "url=%1"
set "folder_name=%2"
set "archive_name=%3"

if not exist "%folder_name%" (
    echo Downloading %url%...
    curl -L -o %archive_name% %url%
    echo Extracting %archive_name%...
    powershell Expand-Archive -Path %archive_name% -DestinationPath "%folder_name%"
    echo Cleaning up...
    del %archive_name%
) else (
    echo Target file already exists, skipping download and extraction.
)
