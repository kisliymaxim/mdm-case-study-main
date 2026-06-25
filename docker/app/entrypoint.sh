#!/bin/sh
set -e

cd /var/www/html

# 1 .env first.
if [ ! -f ".env" ]; then
    echo "[entrypoint] .env missing, copying .env.example"
    cp .env.example .env
fi

# 2 Vendor deps.
if [ ! -f "vendor/autoload.php" ]; then
    if [ "$INSTALL_DEPS" = "true" ]; then
        echo "[entrypoint] leader: running composer install"
        composer install --no-interaction --prefer-dist --optimize-autoloader
    else
        echo "[entrypoint] follower: waiting for leader to install vendor/..."
        WAIT=0
        until [ -f "vendor/autoload.php" ]; do
            WAIT=$((WAIT + 1))
            if [ "$WAIT" -ge 180 ]; then
                echo "[entrypoint] vendor/autoload.php still missing after 180s — giving up"
                exit 1
            fi
            sleep 1
        done
        echo "[entrypoint] vendor ready"
    fi
elif [ "$INSTALL_DEPS" = "true" ]; then
    echo "[entrypoint] leader: refreshing autoload"
    composer dump-autoload --optimize --no-scripts
fi

# 3 APP_KEY.
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "[entrypoint] generating APP_KEY"
    php artisan key:generate --force
fi

# 4 Seed mock JSON if missing.
if [ ! -f "storage/app/jamf/api-mock-response.json" ]; then
    if [ -f ".assignment/api-mock-response.json" ]; then
        echo "[entrypoint] seeding mock JSON from .assignment/"
        mkdir -p storage/app/jamf
        cp .assignment/api-mock-response.json storage/app/jamf/api-mock-response.json
    else
        echo "[entrypoint] WARNING: no mock JSON found (.assignment/api-mock-response.json missing)"
    fi
fi

# 5 Drop any cached config/routes from prior boots so compose env wins.
echo "[entrypoint] clearing cached config + routes"
php artisan optimize:clear || true

# 6 Wait for MySQL via PDO
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

# 7 Migrate (idempotent).
if [ "$RUN_MIGRATIONS" = "true" ]; then
    php artisan migrate --force
fi

# 8 Permissions.
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

exec "$@"
