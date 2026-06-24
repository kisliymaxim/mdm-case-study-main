#!/bin/sh
set -e

cd /var/www/html

# Install deps if vendor missing (first run / fresh clone)
if [ ! -d "vendor" ]; then
    echo "[entrypoint] composer install"
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# .env: copy example if missing, generate key if empty
if [ ! -f ".env" ]; then
    echo "[entrypoint] .env missing, copying .env.example"
    cp .env.example .env
fi

if ! grep -q "^APP_KEY=base64:" .env; then
    echo "[entrypoint] generating APP_KEY"
    php artisan key:generate --force
fi

# Seed mock JSON if missing
if [ ! -f "storage/app/jamf/api-mock-response.json" ] && [ -f "files/api-mock-response.json" ]; then
    mkdir -p storage/app/jamf
    cp files/api-mock-response.json storage/app/jamf/api-mock-response.json
fi

# Drop any cached config/routes from prior boots so compose env wins.
echo "[entrypoint] clearing cached config + routes"
php artisan optimize:clear || true

# Wait for MySQL via PDO. We use PHP (pdo_mysql) instead of mysqladmin
# because Alpine's mysql-client package doesn't always ship the admin
# binary, and we need authenticated reach anyway.
if [ -n "$DB_HOST" ]; then
    echo "[entrypoint] waiting for mysql at $DB_HOST:${DB_PORT:-3306}..."
    ATTEMPTS=0
    until php -r "
        try {
            new PDO(
                'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: 3306) . ';dbname=' . getenv('DB_DATABASE'),
                getenv('DB_USERNAME'),
                getenv('DB_PASSWORD'),
                [PDO::ATTR_TIMEOUT => 2]
            );
            exit(0);
        } catch (Throwable \$e) {
            exit(1);
        }
    "; do
        ATTEMPTS=$((ATTEMPTS + 1))
        if [ "$ATTEMPTS" -ge 60 ]; then
            echo "[entrypoint] mysql still not reachable after 60s — giving up"
            exit 1
        fi
        sleep 1
    done
    echo "[entrypoint] mysql up"
fi

# Migrate (idempotent)
if [ "$RUN_MIGRATIONS" = "true" ]; then
    php artisan migrate --force
fi

# Permissions
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

exec "$@"
