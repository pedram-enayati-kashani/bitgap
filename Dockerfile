# Dockerfile
FROM php:8.2-fpm

# نصب وابستگی‌های سیستمی
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# نصب Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تنظیم دایرکتوری کار
WORKDIR /app

# کپی کد
COPY . .

# نصب وابستگی‌ها
RUN composer install --no-dev --optimize-autoloader

# مجوزهای لازم
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage

EXPOSE 9000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]