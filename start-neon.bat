@echo off
title CCTV CRM - Neon Cloud DB Server
cls
echo ===================================================================
echo   CCTV CRM - Connected to Singapore Neon Cloud Database (Synced)
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
echo Opening Google Chrome to http://127.0.0.1:8001 ...
if exist "C:\Program Files\Google\Chrome\Application\chrome.exe" (
    start "" "C:\Program Files\Google\Chrome\Application\chrome.exe" http://127.0.0.1:8001
) else (
    start chrome http://127.0.0.1:8001 || start http://127.0.0.1:8001
)
echo.
echo Starting Laravel Server...
wsl bash -c "pkill -f 'artisan serve' 2>/dev/null; export PHP_CLI_SERVER_WORKERS=6 && cd /mnt/c/Users/ADMIN/Projects/cctv-crm && php artisan serve --host=0.0.0.0 --port=8001 --no-reload"
pause
