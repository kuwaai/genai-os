@echo off
cd "%~dp0.."

REM Variables for RunHiddenConsole
set "url_RunHiddenConsole=https://github.com/wenshui2008/RunHiddenConsole/releases/download/1.0/RunHiddenConsole.zip"
for %%I in ("%url_RunHiddenConsole%") do set "filename_RunHiddenConsole=%%~nxI"
set "RunHiddenConsole_folder=%filename_RunHiddenConsole:~0,-4%"

REM Variables for Node.js
set "url_NodeJS=https://nodejs.org/dist/v20.11.1/node-v20.11.1-win-x64.zip"
for %%I in ("%url_NodeJS%") do set "filename_NodeJS=%%~nxI"
set "node_folder=%filename_NodeJS:~0,-4%"
for /f "tokens=2 delims=-" %%v in ("%filename_NodeJS%") do set "version_NodeJS=%%v"

REM Variables for PHP
set "url_PHP=https://windows.php.net/downloads/releases/php-8.1.29-Win32-vs16-x64.zip"
for %%I in ("%url_PHP%") do set "filename_PHP=%%~nxI"
set "php_folder=%filename_PHP:~0,-4%"
for /f "tokens=2 delims=-" %%v in ("%filename_PHP%") do set "version_PHP=%%v"

REM Variables for PHP archive
set "url_PHP_Archive=https://windows.php.net/downloads/releases/archives/php-8.1.28-Win32-vs16-x64.zip"
for %%I in ("%url_PHP%") do set "filename_PHP_Archive=%%~nxI"
set "php_folder_Archive=%filename_PHP_Archive:~0,-4%"
for /f "tokens=2 delims=-" %%v in ("%filename_PHP_Archive%") do set "version_PHP_Archive=%%v"

REM Variables for Nginx
set "url_Nginx=https://nginx.org/download/nginx-1.24.0.zip"
for %%I in ("%url_Nginx%") do set "filename_Nginx=%%~nxI"
set "nginx_folder=%filename_Nginx:~0,-4%"
for /f "tokens=2 delims=-" %%v in ("%filename_Nginx%") do set "version_Nginx=%%v"

REM Variables for Python 3.10.12
set "url_Python=https://www.python.org/ftp/python/3.10.11/python-3.10.11-embed-amd64.zip"
for %%I in ("%url_Python%") do set "filename_Python=%%~nxI"
set "python_folder=%filename_Python:~0,-4%"
for /f "tokens=2 delims=-" %%v in ("%filename_Python%") do set "version_Python=%%v"

REM Variables for Redis 6.0.20
set "url_Redis=https://github.com/redis-windows/redis-windows/releases/download/6.0.20/Redis-6.0.20-Windows-x64-msys2.zip"
for %%I in ("%url_Redis%") do set "filename_Redis=%%~nxI"
set "redis_folder=%filename_Redis:~0,-4%"
for /f "tokens=2 delims=-" %%v in ("%filename_Redis%") do set "version_Redis=%%v"

REM Variables for XpdfReader
set "url_XpdfReader=https://dl.kuwaai.org/packages/xpdf/xpdf-tools-win-4.05.zip"
for %%I in ("%url_XpdfReader%") do set "filename_XpdfReader=%%~nxI"
set "xpdfreader_folder=%filename_XpdfReader:~0,-4%"
for /f "tokens=2 delims=-" %%v in ("%filename_XpdfReader%") do set "version_XpdfReader=%%v"

REM Variables for Antiword
set "url_antiword=https://dl.kuwaai.org/packages/antiword/antiword-0.37-windows.zip"
for %%I in ("%url_antiword%") do set "filename_antiword=%%~nxI"
set "antiword_folder=%filename_antiword:~0,-4%"
for /f "tokens=2 delims=-" %%v in ("%filename_antiword%") do set "version_antiword=%%v"
set "HOME=%~dp0..\packages\%antiword_folder%\"

REM Variables for git bash
set "url_gitbash=https://github.com/git-for-windows/git/releases/download/v2.45.1.windows.1/PortableGit-2.45.1-64-bit.7z.exe"
for %%I in ("%url_gitbash%") do set "filename_gitbash=%%~nxI"
set "gitbash_folder=%filename_gitbash:~0,-7%"
for /f "tokens=2 delims=-" %%v in ("%filename_gitbash%") do set "version_gitbash=%%v"

REM Variables for FFmpeg
set "url_ffmpeg=https://www.gyan.dev/ffmpeg/builds/packages/ffmpeg-7.0.1-essentials_build.zip"
for %%I in ("%url_ffmpeg%") do set "filename_ffmpeg=%%~nxI"
set "ffmpeg_folder=%filename_ffmpeg:~0,-4%"
for /f "tokens=2 delims=-" %%v in ("%filename_ffmpeg%") do set "version_ffmpeg=%%v"

REM Environment variables for model cache
set "KUWA_CACHE=%~dp0..\cache"
mkdir "%KUWA_CACHE%"
set "XDG_CACHE_HOME=%KUWA_CACHE%"
set "PIP_CACHE_DIR=%KUWA_CACHE%\pip"
set "TORCH_HOME=%KUWA_CACHE%\torch"
set "CSIDL_LOCAL_APPDATA=%KUWA_CACHE%\appdata"
set "HF_HOME=%KUWA_CACHE%\huggingface"
set "CACHE_PATH_ENV=%KUWA_CACHE%\selenium"
set "PYANNOTE_CACHE=%KUWA_CACHE%\torch\pyannote"

REM Prepare migration file
mkdir src\conf 2>nul
if not exist "src\conf\migrations.txt" (
    type nul > "src\conf\migrations.txt"
)

REM Prepare packages folder
mkdir packages 2>nul

REM init env
set "PATH=%~dp0..\packages\%xpdfreader_folder%\bin64;%~dp0..\packages\%python_folder%\Scripts;%~dp0..\packages\%python_folder%;%~dp0..\packages\%php_folder%;%~dp0..\packages\%node_folder%;%~dp0..\packages\%gitbash_folder%\cmd;%~dp0..\packages\%antiword_folder%\bin;%~dp0..\packages\%ffmpeg_folder%\bin;%PATH%"

if "%1"=="no_migrate" (
    echo Skipped migration
) else (
    REM Run migration
    for %%i in ("src\migration\*.bat") do (
        findstr /i /c:"%%~nxi" "src\conf\migrations.txt" >nul || (
            echo Running %%~nxi
            call "%%i"
            if errorlevel 1 (
                echo %%~nxi did not execute successfully.
            ) else (
                echo %%~nxi executed successfully.
                echo %%~nxi>>"src\conf\migrations.txt"
            )
        )
    )
)

