@echo off
title CCTV CRM - Admin, Technician & Employee ERP Portal (Port 8001)
echo ======================================================================
echo Starting ADMIN, TECHNICIAN & EMPLOYEE ERP SUITE on http://127.0.0.1:8001
echo High-Performance Native Windows Engine
echo ======================================================================
echo Admin ERP Dashboard:   http://127.0.0.1:8001/dashboard
echo Technician Portal:     http://127.0.0.1:8001/technician/dashboard
echo Login Page:            http://127.0.0.1:8001/login
echo.

set "PHP_DIR=C:\Users\ADMIN\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe"
if not exist "%PHP_DIR%\php.exe" (
    set "PHP_BIN=php"
) else (
    set "PHP_BIN=%PHP_DIR%\php.exe"
    set "PATH=%PHP_DIR%;%PATH%"
)
set PHP_CLI_SERVER_WORKERS=8

"%PHP_BIN%" artisan serve --host=0.0.0.0 --port=8001
pause
