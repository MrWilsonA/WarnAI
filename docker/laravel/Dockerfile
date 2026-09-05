FROM php:8.3-fpm-alpine
WORKDIR /var/www/html
RUN apk add --no-cache postgresql-dev libzip-dev oniguruma-dev zip unzip git curl && docker-php-ext-install pdo pdo_pgsql mbstring zip bcmath
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
RUN composer install --no-interaction --no-dev --prefer-dist || true
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache && chmod -R 775 storage bootstrap/cache
CMD ["php-fpm"]
