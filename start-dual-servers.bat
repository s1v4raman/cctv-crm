@echo off
title CCTV CRM - Dual Server Launcher
echo ======================================================================
echo Launching Dual Servers for CCTV CRM:
echo 1. Customer Server:  http://127.0.0.1:8000
echo 2. Staff/Admin Server: http://127.0.0.1:8001
echo ======================================================================
start "Customer Server (Port 8000)" cmd /k "run-customer-server.bat"
start "Admin & Technician Server (Port 8001)" cmd /k "run-admin-server.bat"
echo Both servers started in separate terminal windows!
