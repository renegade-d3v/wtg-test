#!/bin/sh
set -e

# Install composer dependencies if vendor is missing
if [ ! -f /var/www/vendor/autoload.php ]; then
    composer setup
fi

# Create SQLite file if it doesn't exist and it's the active connection
if [ "$DB_CONNECTION" = "sqlite" ] && [ ! -f /var/www/database/database.sqlite ]; then
    touch /var/www/database/database.sqlite
fi

# Retry migration until the database is accepting connections
# (MySQL's first boot takes longer to initialize than compose starts the app)
retries=5
until php artisan migrate --force; do
    retries=$((retries - 1))
    if [ "$retries" -le 0 ]; then
        echo "Database never became available, giving up."
        exit 1
    fi
    echo "Database not ready yet, retrying in 2s..."
    sleep 2
done

exec docker-php-entrypoint php-fpm
