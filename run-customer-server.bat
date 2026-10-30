@echo off
title CCTV CRM - Customer Storefront & Client Portal (Port 8000)
echo ======================================================================
echo Starting CUSTOMER STOREFRONT & CLIENT PORTAL on http://127.0.0.1:8000
echo High-Performance Native Windows Engine
echo ======================================================================
echo Customer Link: http://127.0.0.1:8000/
echo Client Portal: http://127.0.0.1:8000/customer/portal
echo.

set "PHP_DIR=C:\Users\ADMIN\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe"
if not exist "%PHP_DIR%\php.exe" (
    set "PHP_BIN=php"
) else (
    set "PHP_BIN=%PHP_DIR%\php.exe"
    set "PATH=%PHP_DIR%;%PATH%"
)
set PHP_CLI_SERVER_WORKERS=8

"%PHP_BIN%" artisan serve --host=0.0.0.0 --port=8000
pause
