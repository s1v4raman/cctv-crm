@echo off
title CCTV CRM - Local PostgreSQL Server (Ultra Fast)
cls
echo ===================================================================
echo   CCTV CRM - Connected to Local PostgreSQL (127.0.0.1:5432)
echo ===================================================================
echo.
echo   [!] ALWAYS USE THIS ADDRESS IN YOUR BROWSER:
echo       --^> http://127.0.0.1:8001
echo.
echo   [!] KEEP THIS WINDOW OPEN while using the application!
echo ===================================================================
echo.
echo [1/2] Opening Google Chrome to http://127.0.0.1:8001 in 3 seconds...
start "" cmd /c "timeout /t 3 /nobreak >nul & if exist ""C:\Program Files\Google\Chrome\Application\chrome.exe"" (start """" ""C:\Program Files\Google\Chrome\Application\chrome.exe"" http://127.0.0.1:8001) else (start chrome http://127.0.0.1:8001 || start http://127.0.0.1:8001)"
echo.
echo [2/2] Starting server inside WSL...
wsl bash /mnt/c/Users/ADMIN/Projects/cctv-crm/serve-local.sh
pause
