REM This patch will install the dependency of the Painter (Stable Diffusion) executor
if exist "packages\%python_folder%\Scripts\pip.exe" (
	pushd ..\src\executor\image_generation
	pip install --default-timeout=1000 -r requirements.txt
	popd
	exit /b 0
) else (
    echo "Skipped due to pip not yet installed"
	exit /b 1
)
