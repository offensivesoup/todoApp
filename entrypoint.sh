#!/bin/sh

if [ -f /etc/secrets/.env.production ]; then
  cp /etc/secrets/.env.production /var/www/html/.env
fi

cd /var/www/html
php artisan config:clear
php artisan config:cache

php artisan migrate

exec apache2-foreground
