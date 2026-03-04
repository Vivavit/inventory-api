FROM php:8.2-cli

# Install system dependencies + ALL extensions first
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev \
    libonig-dev libxml2-dev default-mysql-client \
    libicu-dev g++ \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo pdo_mysql mbstring zip exif pcntl bcmath gd intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies (ignore platform reqs as safety net)
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs

# Copy rest of project
COPY . .

# Autoload dump
RUN composer dump-autoload --optimize --ignore-platform-reqs

# Set permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 10000

CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan migrate --force && \
    php artisan db:seed --force && \
    php artisan storage:link && \
    php artisan serve --host=0.0.0.0 --port=10000