REM This patch will install the dependency of the Painter (Stable Diffusion) executor
set pip_exe=..\packages\%python_folder%\Scripts\pip.exe
if exist "!pip_exe!" (
	pushd ..\src\executor\image_generation
	!pip_exe! install -r requirements.txt
	popd
	exit /b 0
) else (
    echo "Skipped due to pip not yet installed"
	exit /b 1
)
