@echo off
setlocal
call env.bat
set "source=../../src/multi-chat/lang"
set "destination=translated"
rd %destination% /S /Q
mkdir %destination%
for /d %%i in (%source%\*) do (
	if not "%%~nxi" == "zh_tw" (
		REM ..\translate.py %source%/zh_tw.json %destination%/%%~nxi.json "This is laravel lang json file, Please translate this lang file into %%~nxi language."
		mkdir %destination%\%%~nxi
		for %%j in (%source%\zh_tw\*) do (
			if "%%~nxj" == "store.php" (
				..\translate.py %source%/zh_tw/%%~nxj %destination%/%%~nxi/%%~nxj "This is laravel lang php file, Please translate this lang file into %%~nxi language."
			)
		)
	)
)
endlocal