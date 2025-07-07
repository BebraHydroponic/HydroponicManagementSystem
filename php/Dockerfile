FROM php:8.2-fpm

# Install system dependencies and PHP extensions needed for Symfony and PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev

RUN docker-php-ext-install pdo pdo_pgsql

# Install Composer globally (from the official Composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set the working directory (matches your mapped backend/ folder)
WORKDIR /var/www/html

# Optional: Set correct permissions for development
RUN chown -R www-data:www-data /var/www/html

# Expose the php-fpm port
EXPOSE 9000
