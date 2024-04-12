@echo off
setlocal EnableDelayedExpansion
echo Now in: %cd%
REM Extract the folder name from the input
for %%e in ("%cd%.") do set "current_folder=%%~nxe"

REM Define an array to store the model types and their names
set "names[1]=ChatGPT"
set "names[2]=Gemini Pro"
set "names[3]=GGUF Model"
set "names[4]=HuggingFace Model"
set "names[5]=Custom Module"

REM Define an array to store the model types and their names
set "models[1]=chatgpt"
set "models[2]=geminipro"
set "models[3]=llamacpp"
set "models[4]=huggingface"
set "models[5]=custom"

REM Check if the current folder matches any option
for %%a in (1 2 3 4) do (
	if "!models[%%a]!"=="!current_folder!" (
		echo Using predefined...
		echo EXECUTOR_TYPE=!models[%%a]!
		echo EXECUTOR_NAME=!names[%%a]!
		echo EXECUTOR_ACCESS_CODE=!models[%%a]!
		
		set "EXECUTOR_TYPE=!models[%%a]!"
		set "EXECUTOR_NAME=!names[%%a]!"
		set "EXECUTOR_ACCESS_CODE=!models[%%a]!"
		goto skip_selection
	)
)

REM Display the options
echo Select an option:

for %%a in (1 2 3 4 5) do (
	echo %%a - !names[%%a]!
)

REM Ask for user input
:input_option
set /p "option=Enter the option number (1-5): "
if not defined models[%option%] (
    echo Invalid option. Please try again.
    goto input_option
)

REM Set the model type based on the selected option
set "EXECUTOR_TYPE=!models[%option%]!"

if "!option!" == "5" (
    REM Ask for worker path (must-fill field)
    :input_worker_path
    set /p "worker_path=Enter the worker path: "
    if "!worker_path!"=="" (
        echo Worker path cannot be blank. Please try again.
        goto input_worker_path
    )
)

REM Ask for model name
:input_EXECUTOR_NAME
set /p "EXECUTOR_NAME=Enter the model name: "
if "!EXECUTOR_NAME!"=="" (
    echo Model name cannot be blank. Please try again.
    goto input_EXECUTOR_NAME
)

REM Ask for access code (must-fill field)
:input_EXECUTOR_ACCESS_CODE
set /p "EXECUTOR_ACCESS_CODE=Enter the access code: "
if "!EXECUTOR_ACCESS_CODE!"=="" (
    echo Access code cannot be blank. Please try again.
    goto input_EXECUTOR_ACCESS_CODE
)

:skip_selection

REM Ask for API key if the model type is geminipro or ChatGPT
if "!EXECUTOR_TYPE!"=="geminipro" (
    set "api_key="
    :input_api_key
    set /p "api_key=Enter the API key (press Enter to leave blank): "
    if "!api_key!"=="" goto continue
) else if "!EXECUTOR_TYPE!"=="chatgpt" (
    set "api_key="
    :input_api_key
    set /p "api_key=Enter the API key (press Enter to leave blank): "
    if "!api_key!"=="" goto continue
)

:continue

REM Ask for model path if the model type is llamacpp or Hugging Face
if "!EXECUTOR_TYPE!"=="llamacpp" (
	for /r %%i in (*.gguf) do (
		echo "using founded .gguf file"
		echo model_path=%%~fi
		set "model_path=%%~fi"
		goto skip_model_path
	)

    :input_model_path
    set /p "model_path=Enter the model path: "
    if "!model_path!"=="" (
        echo Model path cannot be blank. Please try again.
        goto input_model_path
    )
) else if "!EXECUTOR_TYPE!"=="huggingface" (
	for /r %%i in (*.model *.bin *.safetensor) do (
		echo "model folder detected, using current folder path"
		echo model_path=%%~dpi
		set "model_path=%%~dpi"
		goto skip_model_path
	)
    :input_model_path
    set /p "model_path=Enter the model path: "
    if "!model_path!"=="" (
        echo Model path cannot be blank. Please try again.
        goto input_model_path
    )
)

:skip_model_path

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

:input_image_path
set /p "image_path=Enter the image path: (press Enter to leave blank)"

:skip_image_path

:input_command_path
set /p "command=Enter the command parameters: (press Enter to leave blank if you don't need or don't know what this is.)"

:skip_command_path
	
del run.bat

REM Save configuration to run.bat
echo set EXECUTOR_ACCESS_CODE=!EXECUTOR_ACCESS_CODE!> run.bat

REM model:config
echo pushd ..\..\..\src\multi-chat>>run.bat
set command=php artisan model:config "!EXECUTOR_ACCESS_CODE!" "!EXECUTOR_NAME!"
IF DEFINED image_path (
    set command=%command% --image "!image_path!"
)
echo %command%>> run.bat
echo popd>>run.bat

REM kuwa-executor
IF NOT "!EXECUTOR_TYPE!"=="custom" (
	set command=start /b "" "kuwa-executor" "!EXECUTOR_TYPE!" "--access_code" "!EXECUTOR_ACCESS_CODE!"
	IF DEFINED api_key (
		set command=%command% "--api_key" "!api_key!"
	)
	IF DEFINED model_path (
		set command=%command% "--model_path" "!model_path!"
	)
) else (
	start /b "" "python" !worker_path! "--access_code" "!EXECUTOR_ACCESS_CODE!"
)
echo %command%>> run.bat

echo Configuration saved to run.bat
pause