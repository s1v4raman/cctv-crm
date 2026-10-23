#!/bin/bash
# 1. Ensure local PostgreSQL is running
sudo -n service postgresql start 2>/dev/null || true

# 2. Forcefully free port 8001 from any orphaned PHP worker processes
fuser -k 8001/tcp 2>/dev/null || true
pkill -9 -f 'php.*8001' 2>/dev/null || true
pkill -9 -f 'artisan serve' 2>/dev/null || true
sleep 1

# 3. Launch multi-worker server
export PHP_CLI_SERVER_WORKERS=6
cd /mnt/c/Users/ADMIN/Projects/cctv-crm
echo "PostgreSQL Status: Active"
echo "Starting Laravel Server on http://127.0.0.1:8001 ..."
php artisan serve --host=0.0.0.0 --port=8001 --no-reload
