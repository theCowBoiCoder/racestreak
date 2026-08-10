FROM composer:2 AS vendor

WORKDIR /app

COPY backend/ /app/

RUN APP_NAME=RaceStreak \
    APP_VERSION=0.1.0 \
    APP_TIMEZONE=UTC \
    APP_URL=http://localhost:8000 \
    composer install --no-interaction --prefer-dist --optimize-autoloader

FROM php:8.4-cli-alpine

RUN apk add --no-cache icu-dev libpq-dev oniguruma-dev \
    && docker-php-ext-install intl mbstring pcntl pdo_pgsql

WORKDIR /var/www/html

COPY --from=vendor /app /var/www/html

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
