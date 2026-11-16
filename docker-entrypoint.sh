#!/bin/bash
set -e

# Đảm bảo database sqlite tồn tại: lần đầu -> migrate + seed dữ liệu demo
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
    chmod 775 /var/www/html/database/database.sqlite
    php artisan migrate --seed --force
else
    # File đã có (ví dụ gắn persistent disk): chỉ áp các migration mới, không seed lại
    php artisan migrate --force
fi

# Tối ưu hóa cache
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec "$@"
