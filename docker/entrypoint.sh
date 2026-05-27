#!/bin/sh
set -e

if [ "$APP_ENV" = "prod" ]; then
    echo "Warming up cache (prod)..."
    php bin/console cache:warmup
fi

exec apache2-foreground
