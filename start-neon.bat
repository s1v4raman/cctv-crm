@echo off
title CCTV CRM - Neon Cloud DB Server
cls
echo ===================================================================
echo   CCTV CRM - Connected to Live Neon PostgreSQL Cloud Database
echo ===================================================================
echo.
echo   [!] ALWAYS USE THIS ADDRESS IN YOUR BROWSER:
echo       --^> http://127.0.0.1:8001
echo.
echo   [!] DO NOT open 0.0.0.0 (Windows does not support 0.0.0.0)
echo.
echo   [!] KEEP THIS WINDOW OPEN while using the application!
echo       Closing this window will stop the server.
echo ===================================================================
echo.
echo Opening browser to http://127.0.0.1:8001 ...
start http://127.0.0.1:8001
echo.
echo Starting Laravel Server...
wsl bash -c "pkill -f 'artisan serve' 2>/dev/null; export PHP_CLI_SERVER_WORKERS=6 && cd /mnt/c/Users/ADMIN/Projects/cctv-crm && php artisan serve --host=0.0.0.0 --port=8001 --no-reload"
pause
