@echo off
cd /d "%~dp0"
title CCTV CRM - Admin Server (Port 8001)
echo ======================================================================
echo Starting ADMIN SERVER on http://admin.localhost:8001
echo The sign-in page here shows only the admin and employee login, and only those
echo accounts can sign in (technicians and daily workers use
echo run-technician-server.bat).
echo ======================================================================
echo Admin Login:           http://admin.localhost:8001/login
echo Admin ERP Dashboard:   http://admin.localhost:8001/dashboard
echo.
echo Use admin.localhost (not 127.0.0.1) so this sign-in stays separate
echo from the technician server in the same browser. Ignore the 0.0.0.0 address
echo printed below: browsers can't open it. The sign-in page opens by itself.
echo.

if not exist ".env" (
    echo No .env file in %CD% - the server cannot start without one.
    pause
    exit /b 1
)

rem Another server on 8001 (such as start-local-postgres.bat) would answer instead, from another folder and database
netstat -ano | findstr /r /c:":8001 .*LISTENING" >nul
if not errorlevel 1 (
    echo Port 8001 is already used by another server, for example start-local-postgres.bat.
    echo Close that server first, so the admin and technician servers share this folder's database.
    pause
    exit /b 1
)

set "PHP_DIR=C:\Users\ADMIN\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.NTS.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe"
if not exist "%PHP_DIR%\php.exe" (
    set "PHP_BIN=php"
) else (
    set "PHP_BIN=%PHP_DIR%\php.exe"
    set "PATH=%PHP_DIR%;%PATH%"
)
set PHP_CLI_SERVER_WORKERS=8
set LOGIN_PORTAL=admin

rem Opens the sign-in page in the browser once the server is up
start "" /b powershell -NoProfile -Command "Start-Sleep -Seconds 3; Start-Process 'http://admin.localhost:8001/login'"

"%PHP_BIN%" artisan serve --host=0.0.0.0 --port=8001
pause
