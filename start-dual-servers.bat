@echo off
title CCTV CRM - Server Launcher
echo ======================================================================
echo Launching Servers for CCTV CRM:
echo 1. Customer Server:    http://127.0.0.1:8000
echo 2. Admin Server:       http://admin.localhost:8001/login
echo 3. Technician Server:  http://technician.localhost:8002/login
echo ======================================================================
start "Customer Server (Port 8000)" cmd /k "run-customer-server.bat"
start "Admin Server (Port 8001)" cmd /k "run-admin-server.bat"
start "Technician Server (Port 8002)" cmd /k "run-technician-server.bat"
echo All servers started in separate terminal windows!
