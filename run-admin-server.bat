@echo off
title CCTV CRM - Admin, Technician & Employee ERP Portal (Port 8001)
echo ======================================================================
echo Starting ADMIN, TECHNICIAN & EMPLOYEE ERP SUITE on http://127.0.0.1:8001
echo ======================================================================
echo Admin ERP Dashboard:   http://127.0.0.1:8001/dashboard
echo Technician Portal:     http://127.0.0.1:8001/technician/dashboard
echo Login Page:            http://127.0.0.1:8001/login
echo.
php artisan serve --host=0.0.0.0 --port=8001
pause
