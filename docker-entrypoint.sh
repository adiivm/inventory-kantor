#!/bin/sh

service cron start

php artisan queue:work --tries=3 --timeout=0 &
php artisan schedule:run >> /dev/null 2>&1 &

apache2-foreground
