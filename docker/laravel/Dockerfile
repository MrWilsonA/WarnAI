FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev

RUN docker-php-ext-install pdo pdo_pgsql mbstring zip bcmath pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/laravel/php.ini /usr/local/etc/php/conf.d/custom.ini

COPY backend-laravel /var/www/html

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/app/shared bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
