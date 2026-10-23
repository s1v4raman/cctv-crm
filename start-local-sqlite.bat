@echo off
title CCTV CRM - Local SQLite Server
echo ========================================================
echo   Starting CCTV CRM on http://127.0.0.1:8001 (SQLite)
echo ========================================================
echo.
php artisan serve --port=8001
pause
