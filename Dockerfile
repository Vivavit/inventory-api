FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev \
    libonig-dev libxml2-dev default-mysql-client \
    libicu-dev g++ nodejs npm \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo pdo_mysql mbstring zip exif pcntl bcmath gd intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --no-autoloader

COPY package.json package-lock.json ./

RUN npm install

COPY . .

RUN npm run build

RUN composer dump-autoload --optimize

COPY ca.pem /var/www/ca.pem

RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 10000

CMD ["/usr/local/bin/docker-entrypoint.sh"]