:: This script will get proxies from the registry and write to environment variables.
:: Ref: https://github.com/python/cpython/blob/d86ab5dde286642a378fcc32c243bc3b4bed750d/Lib/urllib/request.py#L2684

@echo off
setlocal enableDelayedExpansion

:: Check if reg query command is successful
reg query "HKCU\Software\Microsoft\Windows\CurrentVersion\Internet Settings" /v ProxyEnable >nul 2>&1
if %errorlevel% neq 0 goto :eof

:: Get ProxyEnable value
for /f "tokens=3" %%a in ('reg query "HKCU\Software\Microsoft\Windows\CurrentVersion\Internet Settings" /v ProxyEnable ^| findstr /i "ProxyEnable"') do set "proxyEnable=%%a"

:: Check if proxy is enabled
if "!proxyEnable!" neq "0x1" (
    set proxyEnable=
    echo Proxy Related Environment Variables:
    set | findstr /i _proxy
    echo ---
    goto :eof
)

:: Get ProxyServer value
for /f "tokens=3" %%a in ('reg query "HKCU\Software\Microsoft\Windows\CurrentVersion\Internet Settings" /v ProxyServer ^| findstr /i "ProxyServer"') do set "proxyServer=%%a"

:: Check if the ProxyServer does not match the form <scheme>=<proxy>.
echo !ProxyServer! | findstr "=" >nul
if %errorlevel% neq 0 (
    :: Use one setting for all protocols
    set "proxyServer=http=!proxyServer!;https=!proxyServer!;ftp=!proxyServer!"
)

:: Parse proxy settings
for %%p in ("!proxyServer:;=" "!") do (
    :: Default scheme is http
    for /f "tokens=1,2 delims==" %%a in (%%p) do (
        set "scheme=%%a"
        set "address=%%b"
    )

    :: Add type:// prefix if missing
    if "!address:://=!" equ "!address!" (
        if "!scheme!" equ "http" set "address=http://!address!"
        if "!scheme!" equ "https" set "address=https://!address!"
        if "!scheme!" equ "ftp" set "address=ftp://!address!"
        if "!scheme!" equ "socks" set "address=socks4://!address!"
    )

    :: Set proxy if it's not set
    set "proxyName=!scheme!_proxy"
    for /f %%a in ("!proxyName!") do (set "oldProxyValue=!%%a!")
    if "!oldProxyValue!" equ "" (
        set "!proxyName!=!address!"
    )
)

:: Use SOCKS proxy for HTTP(S) if available
if "!socks_proxy!" neq "" (
    if "!http_proxy!" equ ""  set "http_proxy=!socks_proxy!"
    if "!https_proxy!" equ "" set "https_proxy=!socks_proxy!"
)

:: Parse no_proxy configuration
for /f "tokens=3" %%a in ('reg query "HKCU\Software\Microsoft\Windows\CurrentVersion\Internet Settings" /v ProxyOverride ^| findstr /i "REG_SZ"') do set "no_proxy=%%a"
set "no_proxy=!no_proxy:;=,!"
set "no_proxy=!no_proxy:<local>=localhost,127.0.0.0/8!"

::Clean up
set proxyEnable=
set proxyServer=
set proxyName=
set oldProxyValue=

echo Proxy Related Environment Variables:
set | findstr /i _proxy
echo ---

:: Expose all proxy related variables
for /f %%v in ('set ^| findstr /i _proxy') do (
    endlocal
    set %%v
)