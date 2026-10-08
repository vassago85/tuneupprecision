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

# The queue and scheduler containers share this image and pass their own
# command. Docker hands it to this script as arguments; without this branch
# they would fall through to supervisord and run a second web server instead
# of the worker, leaving every queued email unsent.
if [ "$#" -gt 0 ]; then
    php artisan config:cache || true
    chown -R www-data:www-data /var/www/html/storage/logs /var/www/html/bootstrap/cache
    echo "Starting worker: $*"
    exec su-exec www-data "$@"
fi

# Migrations
echo "Running migrations..."
php artisan migrate --force || echo "Migration had issues, continuing..."

# Guard against placeholder contact / VAT / dealer values.
# Default: warn and continue so a missing LEGAL_* value cannot take the site down.
# Set LEGAL_ENFORCE=true in .env only after Dirk has filled the real identity.
echo "Checking legal identity..."
if [ "${LEGAL_ENFORCE}" = "true" ]; then
    php artisan legal:check
else
    php artisan legal:check || echo "legal:check failed — site will start anyway. Set LEGAL_* in .env, then LEGAL_ENFORCE=true when ready."
fi

# Optionally seed on first boot (set RUN_SEED=true in env for the very first deploy)
if [ "${RUN_SEED}" = "true" ]; then
    echo "Seeding database (RUN_SEED=true)..."
    php artisan db:seed --force || echo "Seed had issues, continuing..."
fi

# Clear + warm caches (env comes from Docker at runtime)
echo "Preparing for production..."
php artisan optimize:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan event:cache || true
php artisan view:cache || true
php artisan filament:optimize || true

# Publish Livewire + Filament assets and link storage
php artisan livewire:publish --assets 2>/dev/null || true
php artisan filament:assets 2>/dev/null || true
php artisan storage:link 2>/dev/null || true

# Final permissions
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

echo "Tune Up is ready!"

exec /usr/bin/supervisord -c /etc/supervisord.conf
