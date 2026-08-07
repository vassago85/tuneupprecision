#!/bin/sh
set -e

echo "Starting Tune Up..."

# Ensure runtime directories exist
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Permissions
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/bootstrap/cache

# Wait for the database port
echo "Waiting for database (db:5432)..."
max_tries=30
count=0
until nc -z db 5432 2>/dev/null; do
    count=$((count + 1))
    if [ $count -ge $max_tries ]; then
        echo "Database not reachable after $max_tries attempts"
        exit 1
    fi
    echo "  Attempt $count/$max_tries - waiting for db:5432..."
    sleep 2
done
echo "Database port is open"

sleep 3

# Verify credentials
echo "Testing database credentials..."
max_tries=10
count=0
until php -r "new PDO('pgsql:host=db;port=5432;dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
    count=$((count + 1))
    if [ $count -ge $max_tries ]; then
        echo "Database authentication failed after $max_tries attempts"
        break
    fi
    echo "  Auth attempt $count/$max_tries..."
    sleep 2
done
echo "Database is ready"

# Migrations
echo "Running migrations..."
php artisan migrate --force || echo "Migration had issues, continuing..."

# Optionally seed on first boot (set RUN_SEED=true in env for the very first deploy)
if [ "${RUN_SEED}" = "true" ]; then
    echo "Seeding database (RUN_SEED=true)..."
    php artisan db:seed --force || echo "Seed had issues, continuing..."
fi

# Clear + warm caches (env comes from Docker at runtime)
echo "Preparing for production..."
php artisan optimize:clear || true
php artisan config:cache || true
php artisan view:cache || true
# NOTE: route:cache is intentionally skipped — routes/web.php uses a closure.

# Publish Livewire + Filament assets and link storage
php artisan livewire:publish --assets 2>/dev/null || true
php artisan filament:assets 2>/dev/null || true
php artisan storage:link 2>/dev/null || true

# Final permissions
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

echo "Tune Up is ready!"

exec /usr/bin/supervisord -c /etc/supervisord.conf
