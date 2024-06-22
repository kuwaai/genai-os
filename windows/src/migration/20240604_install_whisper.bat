REM This patch will install the dependency of the Whisper executor

REM Variables for FFmpeg
set "url_ffmpeg=https://www.gyan.dev/ffmpeg/builds/packages/ffmpeg-7.0.1-essentials_build.zip"
for %%I in ("%url_ffmpeg%") do set "filename_ffmpeg=%%~nxI"
set "ffmpeg_folder=%filename_ffmpeg:~0,-4%"
for /f "tokens=2 delims=-" %%v in ("%filename_ffmpeg%") do set "version_ffmpeg=%%v"

REM Download and extract FFmpeg if not exists
call src\download_extract.bat %url_ffmpeg% packages\%ffmpeg_folder% packages\. ffmpeg.zip

set pip_exe=..\packages\%python_folder%\Scripts\pip.exe
if exist "!pip_exe!" (
	pushd ..\src\executor\speech_recognition
	pip install https://www.piwheels.org/simple/docopt/docopt-0.6.2-py2.py3-none-any.whl#sha256=15fde8252aa9f2804171014d50d069ffbf42c7a50b7d74bcbb82bfd5700fcfc2
	pip install -r requirements.txt
	popd
	exit /b 0
) else (
    echo "Skipped due to pip not yet installed"
	exit /b 1
)