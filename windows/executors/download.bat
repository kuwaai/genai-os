@echo off
cd "%~dp0"
if "%1"=="quick" (
	call ..\src\variables.bat no_migrate
) else (
	call ..\src\variables.bat
)
cd "%~dp0"
if "%1"=="quick" (
    goto function2
)
setlocal EnableDelayedExpansion


REM Define an array to store the model types and their names
set "names[1]=Whisper Model"
set "names[2]=TAIDE Model"
set "names[3]=Stable Diffusion Model"
set "names[4]=Embedding Model"
REM set "names[5]=GGUF Model"
REM set "names[6]=HuggingFace Model"
set "names[5]=Exit"

REM Define an array to store the model types and their names
set "models[1]=whisper"
set "models[2]=taide"
set "models[3]=stable_diffusion"
set "models[4]=embedding_model"
REM set "models[5]=gguf_model"
REM set "models[6]=huggingface"
set "models[5]=exit"
:main
cls
echo Now in: "%cd%"

echo Download Model:

for %%a in (1 2 3 4 5) do (
    echo %%a - !names[%%a]!
    REM if "%%a" == "4" (
    REM     echo ------------
    REM )
    if "%%a" == "4" (
        echo ------------
    )
)
set /p "option=Enter the option number (1-5): "
if not defined models[%option%] (
    echo Invalid option. Please try again.
    goto main
)
set "EXECUTOR_TYPE=!models[%option%]!"

if "%option%"=="1" (
    :function1
    set userInput=n
    set /p "userInput=璶更 Whisper Medium 家盾 ( 1.4GB) [y/N] "
    
    if /I "%userInput%"=="y" (
    	echo タ更家...
		set python_exe=..\packages\%python_folder%\python.exe
		if exist "!python_exe!" (
			!python_exe! ../../src/executor/speech_recognition/download_model.py
		) else (
			echo ぶ赣郎 !python_exe! 叫磅︽Ч拨build.bat
		)
		echo 更Ч拨
	) else (
		echo 盢ぃ穦更赣家
	)
    pause
) else if "%option%"=="2" (
    :function2
    set userInput=n
    set /p "userInput=璶更 Llama3-TAIDE-LX-8B-Chat-Alpha1.Q4_K_M  GGUF 家盾 ( 4.7GB) [y/N] "
    
    if /I "%userInput%"=="y" (
    	echo タ更家...
    	curl -L -o "taide/taide-8b-a.3-q4_k_m.gguf" https://huggingface.co/nctu6/Llama3-TAIDE-LX-8B-Chat-Alpha1-GGUF/resolve/main/Llama3-TAIDE-LX-8B-Chat-Alpha1-Q4_K_M.gguf?download=true
		echo 更Ч拨
	) else (
		echo 盢ぃ穦更赣家
	)
	if "%1"=="quick" (
		exit
	)
    pause
) else if "%option%"=="3" (
    :function3
    set userInput=n
    set /p "userInput=璶更 Stable diffusion 2 家盾 ( 4.8GB) [y/N] "
    
    if /I "%userInput%"=="y" (
    	echo タ更家...
		set python_exe=..\packages\%python_folder%\python.exe

		if exist "%python_exe%" (
			%python_exe% ../../src/executor/image_generation/download_model.py
		) else (
			echo ぶ赣郎 !python_exe! 叫磅︽Ч拨build.bat
		)
		echo 更Ч拨
	) else (
		echo 盢ぃ穦更赣家
	)
    pause
) else if "%option%"=="4" (
    :function4
	set userInput=n
    set /p "userInput=璶更 infgrad/stella-base-zh 家盾 ( 196.5MB) [y/N] "
    
    if /I "%userInput%"=="y" (
    	echo タ更家...
		set python_exe=..\packages\%python_folder%\python.exe

		if exist "%python_exe%" (
			%python_exe% ../../src/executor/docqa/download_model.py
		) else (
			echo ぶ赣郎 !python_exe! 叫磅︽Ч拨build.bat
		)
		echo 更Ч拨
	) else (
		echo 盢ぃ穦更赣家
	)
    pause
) else if "%option%"=="5" (
    exit /b
)

goto main
