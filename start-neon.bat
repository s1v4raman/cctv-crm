@echo off
title CCTV CRM - Neon Cloud DB Server (High Performance Native Windows)
cls
echo ===================================================================
echo   CCTV CRM - Connected to Singapore Neon Cloud Database (Synced)
echo   [!] Native Windows High-Performance Engine (Blazing Fast)
echo ===================================================================
echo.
echo   [!] Real-Time Sync with Render:
echo       Any changes made locally or on Render will show immediately!
echo.
echo   [!] ALWAYS USE THIS ADDRESS IN YOUR BROWSER:
echo       --^> http://127.0.0.1:8001
echo.
echo   [!] KEEP THIS WINDOW OPEN while using the application!
echo ===================================================================
echo.

set "PHP_DIR=C:\Users\ADMIN\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe"
if not exist "%PHP_DIR%\php.exe" (
    echo [!] Falling back to system PHP...
    set "PHP_BIN=php"
) else (
    set "PHP_BIN=%PHP_DIR%\php.exe"
    set "PATH=%PHP_DIR%;%PATH%"
)

set PHP_CLI_SERVER_WORKERS=8

echo Opening Google Chrome to http://127.0.0.1:8001 ...
if exist "C:\Program Files\Google\Chrome\Application\chrome.exe" (
    start "" "C:\Program Files\Google\Chrome\Application\chrome.exe" http://127.0.0.1:8001
) else (
    start chrome http://127.0.0.1:8001 || start http://127.0.0.1:8001
)
echo.
echo Starting Native Windows Laravel Server on http://127.0.0.1:8001 ...
"%PHP_BIN%" artisan serve --host=0.0.0.0 --port=8001
pause
