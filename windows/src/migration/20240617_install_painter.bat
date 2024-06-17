REM This patch will install the dependency of the Painter (Stable Diffusion) executor

pushd ..\src\executor\image_generation
pip install -r requirements.txt
popd

exit /b 0