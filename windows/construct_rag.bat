@echo off
if not defined in_subprocess (cmd /U /k set in_subprocess=y ^& %0 %* & exit)
setlocal EnableDelayedExpansion

cd /D "%~dp0"
echo Now in: "%cd%"
call src\variables.bat
set "PATH=%~dp0packages\%python_folder%;%~dp0packages\%python_folder%\Scripts;%PATH%"

REM Construct the database

set TARGET=%~1

REM Extract the folder name from the input
if "!TARGET!"=="" (
    echo Please drag the data folder to this script.
    pause
    exit /b 0
)
for %%f in ("!TARGET!") do set "database_name=%%~nxf"
set "access_code=db-qa-!database_name!"
echo "Database name: !database_name!"
mkdir ".\executors\!database_name!"

pushd "%~dp0\..\src\toolchain"
python construct_vector_db.py "!TARGET!" "..\..\windows\executors\!database_name!\db"
popd

REM Setup the executor

cd ".\executors\!database_name!"
REM Remove variables
set EXECUTOR_TYPE=
set EXECUTOR_NAME=
set EXECUTOR_ACCESS_CODE=
set api_key=
set model_path=
set worker_path=
set arguments=
set command=
set image_path=
set target_access_code=

echo EXECUTOR_TYPE=custom
echo EXECUTOR_NAME=!database_name!
echo EXECUTOR_ACCESS_CODE=!access_code!

set "EXECUTOR_TYPE=custom"
set "EXECUTOR_NAME=!database_name!"
set "EXECUTOR_ACCESS_CODE=!access_code!"
set "worker_path=docqa.py"
for /d %%i in (*) do (
    echo "Folder detected, using founded folder."
    for %%F in ("%%~pi.") do (
        for %%G in ("%%~pi\..") do (
            for %%H in ("%%~pi\..\..") do (
                echo db_path=..\..\..\%%~nxH\%%~nxG\%%~nxF\%%~nxi
                set "db_path=..\..\..\%%~nxH\%%~nxG\%%~nxF\%%~nxi"
                goto skip_db_path
            )
        )
    )
)

echo Database not founded. Aborting
exit /b 0

:skip_db_path

REM Find the executor image
for /r %%i in (*.jpg *.jpeg *.png *.gif *.webp *.bmp *.ico *.svg *.tiff *.tif *.jp2 *.jxr *.wdp *.hdp) do (
    echo "Image detected, using founded image."
    for %%F in ("%%~pi.") do (
		for %%G in ("%%~pi\..") do (
			for %%H in ("%%~pi\..\..") do (
				echo image_path=..\..\%%~nxH\%%~nxG\%%~nxF\%%~nxi
				set "image_path=..\..\%%~nxH\%%~nxG\%%~nxF\%%~nxi"
				goto skip_image_path
			)
		)
	)
)

:skip_image_path

REM Set the addition arguments of the executor
set /p "arguments=Arguments to use: (press Enter to leave blank if you don't need or don't know what this is.)"

REM Save configuration to run.bat

del run.bat
echo set "EXECUTOR_ACCESS_CODE=!EXECUTOR_ACCESS_CODE!"> run.bat

REM model:config
echo pushd ..\..\..\src\multi-chat>>run.bat
set command=php artisan model:config "!EXECUTOR_ACCESS_CODE!" "!EXECUTOR_NAME!"
if DEFINED image_path (
    set command=!command! --image "!image_path!"
)
echo !command!>> run.bat
echo popd>>run.bat

REM kuwa-executor
set command=start /b "" "python" !worker_path! "--access_code" "!EXECUTOR_ACCESS_CODE!"
if DEFINED db_path (
    set command=!command! "--database" "!db_path!"
)
if DEFINED arguments (
    set command=!command! !arguments!
)
echo pushd ..\..\..\src\executor\docqa\>> run.bat
echo !command!>> run.bat
echo popd>> run.bat

echo Configuration saved to run.bat
pause