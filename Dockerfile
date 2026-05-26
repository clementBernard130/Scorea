FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    libpq-dev libicu-dev zip unzip git nodejs npm \
    && docker-php-ext-install pdo pdo_pgsql intl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/html/public/!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN echo "FallbackResource /index.php" >> /etc/apache2/apache2.conf

RUN a2enmod rewrite

WORKDIR /var/www/html

ARG APP_ENV=prod

COPY composer.json composer.lock package.json package-lock.json ./

RUN if [ "$APP_ENV" = "prod" ]; then \
        composer install --no-dev --no-scripts --prefer-dist; \
    else \
        composer install --prefer-dist --no-scripts; \
    fi

RUN npm install

COPY . .

# Compilation des assets et nettoyage
RUN npm run build \
    && composer dump-autoload --optimize \
    && mkdir -p var/cache var/log \
    && chown -R www-data:www-data var/ public/build

RUN chmod +x docker/entrypoint.sh

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]