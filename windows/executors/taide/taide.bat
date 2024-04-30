@echo off
cd "%~dp0"

set userInput=y
set /p userInput=璶腊眤笆更 Llama3-TAIDE-LX-8B-Chat-Alpha1.Q4_K_M  GGUF 家盾 ( 4.7GB) [Y/n] 

if /I "%userInput%"=="n" (
    echo 盢ぃ穦笆更赣家眤更赣家赣戈ず㏑taide-8b-a.3-q4_k_m.gguf
    start .
     pause
) else (
     echo タ更家...
     curl -L -o "taide-8b-a.3-q4_k_m.gguf" https://huggingface.co/ZoneTwelve/Llama3-TAIDE-LX-8B-Chat-Alpha1-GGUF/resolve/main/Llama3-TAIDE-LX-8B-Chat-Alpha1.Q4_K_M.gguf?download=true
     
)