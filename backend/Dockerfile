# Backend Laravel - Imagen de producción
FROM php:8.2-cli-alpine

# Extensiones PHP necesarias para Laravel
RUN apk add --no-cache \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring dom fileinfo zip pcntl

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar dependencias
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize

# Permisos
RUN chmod -R 775 storage bootstrap/cache

# Configuración
RUN php artisan config:cache && php artisan route:cache 2>/dev/null || true

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
