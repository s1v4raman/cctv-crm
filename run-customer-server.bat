@echo off
title CCTV CRM - Customer Storefront & Client Portal (Port 8000)
echo ======================================================================
echo Starting CUSTOMER STOREFRONT & CLIENT PORTAL on http://127.0.0.1:8000
echo ======================================================================
echo Customer Link: http://127.0.0.1:8000/
echo Client Portal: http://127.0.0.1:8000/customer/portal
php artisan serve --host=0.0.0.0 --port=8000
pause
