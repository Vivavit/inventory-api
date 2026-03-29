#!/bin/bash
set -e

echo "==> Clearing old cache..."
php artisan config:clear
php artisan route:clear

echo "==> Caching config & routes..."
php artisan config:cache
php artisan route:cache

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Checking if seeding is needed..."
USER_COUNT=$(php -r "require 'vendor/autoload.php'; echo \App\Models\User::count();" 2>/dev/null)

if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo "==> Seeding database..."
    php artisan db:seed --force
else
    echo "==> Database already seeded ($USER_COUNT users found), skipping."
fi

echo "==> Linking storage..."
php artisan storage:link --quiet 2>/dev/null || true

echo "==> Starting server..."
exec php artisan serve --host=0.0.0.0 --port=10000