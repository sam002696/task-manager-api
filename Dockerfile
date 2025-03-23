# Use official PHP image with required extensions
FROM php:8.2-fpm

# Setting working directory
WORKDIR /var/www

# system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Installing Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copying project files
COPY . .

# Installing PHP dependencies
RUN composer install

# Setting permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Exposing port (optional if not using nginx separately)
EXPOSE 9000

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]



CMD ["php-fpm"]
