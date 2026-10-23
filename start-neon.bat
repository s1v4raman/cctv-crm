@echo off
title CCTV CRM - Neon Cloud DB
echo ========================================================
echo   Starting CCTV CRM on http://127.0.0.1:8001
echo   Connected to Neon Cloud PostgreSQL Database
echo ========================================================
echo.
echo 1. Opening browser to http://127.0.0.1:8001 ...
start http://127.0.0.1:8001
echo.
echo 2. Launching background server window...
echo    NOTE: Please keep the server window open while using the app!
echo.
start "CCTV CRM Server (Port 8001 - Neon DB)" wsl bash -c "cd /mnt/c/Users/ADMIN/Projects/cctv-crm && php artisan serve --host=127.0.0.1 --port=8001"
echo.
echo Done! Your CRM is now live on http://127.0.0.1:8001
ping -n 3 127.0.0.1 >nul

