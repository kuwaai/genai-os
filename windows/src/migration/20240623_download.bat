REM This open the model download window for user when pip is done of downloading
set pip_exe=..\packages\%python_folder%\Scripts\pip.exe
if exist "!pip_exe!" (
	start ..\..\executors\download.bat
	exit /b 0
) else (
    echo "Skipped due to pip not yet installed"
	exit /b 1
)
