#!/bin/bash
export APP_URL=http://192.168.18.134:8000
cd /Users/bobhartanto/te_01
php artisan config:clear
php artisan serve --host=0.0.0.0 --port=8000
