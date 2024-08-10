@echo off
setlocal EnableDelayedExpansion
cd "%~dp0"
if "%1"=="quick" (
	call ..\src\variables.bat no_migrate
) else (
	call ..\src\variables.bat
)
cd "%~dp0"
if "%1"=="quick" (
	if "%2"=="" (
		set option=2
	) else (
		set option=%2
	)
	goto quick_main
)


REM Define an array to store the model types and their names
set "names[1]=Whisper Model"
set "names[2]=TAIDE Model"
set "names[3]=Stable Diffusion Model"
set "names[4]=Diarization Model"
set "names[5]=llama3.1 Model"
REM set "names[4]=Embedding Model"
REM set "names[5]=GGUF Model"
REM set "names[6]=HuggingFace Model"
set "names[6]=Exit"

REM Define an array to store the model types and their names
set "models[1]=whisper"
set "models[2]=taide"
set "models[3]=stable_diffusion"
set "models[4]=diarization"
set "models[5]=llama"
REM set "models[4]=embedding_model"
REM set "models[5]=gguf_model"
REM set "models[6]=huggingface"
set "models[6]=exit"
:main
cls
echo Now in: "%cd%"

echo Download Model:

for %%a in (1 2 3 4 5 6) do (
    echo %%a - !names[%%a]!
    if "%%a" == "5" (
        echo ------------
    )
)
set /p "option=Enter the option number (1-6): "
if not defined models[%option%] (
    echo Invalid option. Please try again.
    goto main
)
:quick_main
set "EXECUTOR_TYPE=!models[%option%]!"

if "%option%"=="1" (
    :function1
    set userInput=n
    set /p "userInput=璶更 Whisper Medium 家盾 ( 1.4GB) [y/N] "
    
    if /I "!userInput!"=="y" (
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
    
    if /I "!userInput!"=="y" (
    	echo タ更家...
    	curl -L -o "taide/taide-8b-a.3-q4_k_m.gguf" https://huggingface.co/nctu6/Llama3-TAIDE-LX-8B-Chat-Alpha1-GGUF/resolve/main/Llama3-TAIDE-LX-8B-Chat-Alpha1-Q4_K_M.gguf?download=true
		echo 更Ч拨
	) else (
		echo 盢ぃ穦更赣家
	)
    pause
) else if "%option%"=="3" (
    :function3
    set userInput=n
    set /p "userInput=璶更 Stable diffusion 2 家盾 ( 4.8GB) [y/N] "
    
    if /I "!userInput!"=="y" (
    	echo タ更家...
		set python_exe=..\packages\%python_folder%\python.exe

		if exist "!python_exe!" (
			!python_exe! ../../src/executor/image_generation/download_model.py
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
    set /p "userInput=璶更 pyannote/speaker-diarization-3.1 家盾 ( 31.2MB) [y/N] "
    
    if /I "!userInput!"=="y" (
    	echo タ更家...
		set python_exe=..\packages\%python_folder%\python.exe

		if exist "!python_exe!" (
			!python_exe! ../../src/executor/speech_recognition/download_model.py --diarizer
		) else (
			echo ぶ赣郎 !python_exe! 叫磅︽Ч拨build.bat
		)
		echo 更Ч拨
	) else (
		echo 盢ぃ穦更赣家
	)
    pause
)  else if "%option%"=="5" (
    :function5
    set userInput=n
    set /p "userInput=璶更 Llama3.1-8B.Q4_K_M  GGUF 家盾 ( 4.7GB) [y/N] "
    
    if /I "!userInput!"=="y" (
    	echo タ更家...
    	curl -L -o "llama3_1/llama3_1-8b-q4_k_m.gguf" https://huggingface.co/chatpdflocal/llama3.1-8b-gguf/resolve/main/ggml-model-Q4_K_M.gguf
		echo 更Ч拨
	) else (
		echo 盢ぃ穦更赣家
	)
    pause
) else if "%option%"=="6" (
    exit
)
if "%1"=="quick" (
	exit /b
)

goto main
