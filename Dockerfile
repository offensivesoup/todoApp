FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git zip unzip curl libpq-dev libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

WORKDIR /var/www/html

RUN composer install --no-dev --optimize-autoloader \
    && php artisan config:cache

EXPOSE 80
