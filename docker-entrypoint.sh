#!/bin/bash
set -e

# Đảm bảo database sqlite tồn tại
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
    chmod 775 /var/www/html/database/database.sqlite
    php artisan migrate --seed --force
fi

# Tối ưu hóa cache
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec "$@"
