@echo off
cd /d "%~dp0"
title CCTV CRM - Technician Server (Port 8002)
echo ======================================================================
echo Starting TECHNICIAN SERVER on http://technician.localhost:8002
echo The sign-in page here has the Technician and Daily Worker tabs, and only
echo those accounts can sign in (admins use run-admin-server.bat).
echo ======================================================================
echo Technician / Worker Login: http://technician.localhost:8002/login
echo Technician Portal:         http://technician.localhost:8002/technician/dashboard
echo Daily Worker Portal:       http://technician.localhost:8002/worker/portal
echo.
echo Use technician.localhost (not 127.0.0.1) so this sign-in stays separate
echo from the admin server in the same browser. Ignore the 0.0.0.0 address
echo printed below: browsers can't open it. The sign-in page opens by itself.
echo.

if not exist ".env" (
    echo No .env file in %CD% - the server cannot start without one.
    pause
    exit /b 1
)

rem Another server on 8002 would answer instead, from another folder and database
netstat -ano | findstr /r /c:":8002 .*LISTENING" >nul
if not errorlevel 1 (
    echo Port 8002 is already used by another server. Close that server first,
    echo so the admin and technician servers share this folder's database.
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
set LOGIN_PORTAL=technician

rem Opens the sign-in page in the browser once the server is up
start "" /b powershell -NoProfile -Command "Start-Sleep -Seconds 3; Start-Process 'http://technician.localhost:8002/login'"

"%PHP_BIN%" artisan serve --host=0.0.0.0 --port=8002
pause
