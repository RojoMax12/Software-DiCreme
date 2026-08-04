# Imagen base de PHP 8.3 con FPM y Nginx
FROM php:8.3-fpm-alpine

# Instalar paquetes y extensiones necesarias para Laravel y MySQL
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    icu-dev \
    mysql-client

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring gd zip intl opcache

# Copiar Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . /var/www/html

# Instalar dependencias PHP de producción sin dev
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permisos de almacenamiento
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configuración de Nginx para Laravel
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

EXPOSE 80

# Comando de inicio
CMD ["sh", "-c", "php artisan storage:link && php artisan config:cache && php artisan route:cache && nginx && php-fpm"]
