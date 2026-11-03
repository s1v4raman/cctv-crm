@echo off
cd /d "%~dp0"
title CCTV CRM - Admin and Technician Server Launcher
echo ======================================================================
echo Launching separate Admin and Technician servers for CCTV CRM:
echo 1. Admin Server:       http://admin.localhost:8001/login
echo 2. Technician Server:  http://technician.localhost:8002/login
echo ======================================================================
start "Admin Server (Port 8001)" cmd /k "run-admin-server.bat"
start "Technician Server (Port 8002)" cmd /k "run-technician-server.bat"
echo Both servers started in separate terminal windows!
