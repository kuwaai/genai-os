@echo off
setlocal EnableDelayedExpansion

REM Extract the folder name from the input
for %%e in ("%~dp0.") do set "current_folder=%%~nxe"

REM Define the options
set "options=1-chatgpt 2-geminipro 3-gguf 4-huggingface"

REM Define an array to store the model types and their names
set "models[1]=chatgpt"
set "models[2]=geminipro"
set "models[3]=gguf"
set "models[4]=huggingface"

REM Check if the current folder matches any option
for %%a in (1 2 3 4) do (
    for /f "tokens=1,* delims=-" %%b in ("!options!") do (
        if "!models[%%a]!"=="!current_folder!" (
            set "model_type=!models[%%a]!"
            goto skip_selection
        )
    )
)

REM Display the options
echo Select an option:
for %%o in (%options%) do (
    for /f "tokens=1,* delims=-" %%a in ("%%o") do echo %%a - !models[%%a]!
)

REM Ask for user input
:input_option
set /p "option=Enter the option number (1-4): "
if not defined models[%option%] (
    echo Invalid option. Please try again.
    goto input_option
)

REM Set the model type based on the selected option
set "model_type=!models[%option%]!"

:skip_selection

REM Ask for model name
:input_model_name
set /p "model_name=Enter the model name: "
if "!model_name!"=="" (
    echo Model name cannot be blank. Please try again.
    goto input_model_name
)

REM Ask for API key if the model type is geminipro or ChatGPT
if "!model_type!"=="geminipro" (
    set "api_key="
    :input_api_key
    set /p "api_key=Enter the API key (press Enter to leave blank): "
    if "!api_key!"=="" goto continue
) else if "!model_type!"=="chatgpt" (
    set "api_key="
    :input_api_key
    set /p "api_key=Enter the API key (press Enter to leave blank): "
    if "!api_key!"=="" goto continue
)

:continue
REM Ask for model path if the model type is GGUF or Hugging Face
if "!model_type!"=="gguf" (
    :input_model_path
    set /p "model_path=Enter the model path: "
    if "!model_path!"=="" (
        echo Model path cannot be blank. Please try again.
        goto input_model_path
    )
) else if "!model_type!"=="huggingface" (
    :input_model_path
    set /p "model_path=Enter the model path: "
    if "!model_path!"=="" (
        echo Model path cannot be blank. Please try again.
        goto input_model_path
    )
)

REM Save to env.bat
echo set "model_type=!model_type!" > env.bat
echo set "model_name=!model_name!" >> env.bat
if defined api_key echo set "api_key=!api_key!" >> env.bat
if defined model_path echo set "model_path=!model_path!" >> env.bat

echo Configuration saved to env.bat
pause
