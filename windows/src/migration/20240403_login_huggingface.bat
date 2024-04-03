REM This help user quickly setup their own account

set python_exe=packages\%python_folder%\python.exe
set "PATH=%~dp0packages\%python_folder%;%~dp0packages\%python_folder%\Scripts;%PATH%"

if exist "%python_exe%" (
    huggingface-cli login
    exit /b 0
) else (
    echo "Python executable not found!"
    exit /b 1
)
