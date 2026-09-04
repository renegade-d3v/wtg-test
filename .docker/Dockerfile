# Use the official PHP 8.4 image with Apache
FROM php:8.4-fpm-alpine

LABEL maintainer="renegade-d3v"

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apk --no-cache add \
    build-base \
    shadow \
    linux-headers \
    libzip-dev \
    zip \
    unzip \
    git \
    bash \
    fcgi \
    libmcrypt-dev \
    oniguruma-dev \
    sqlite-dev \
    libmemcached-dev \
    zlib-dev \
    autoconf \
    icu-dev

# Clear cache
RUN rm -rf /var/cache/apk/*

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_sqlite \
        pdo_mysql \
        sockets \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        intl \
        opcache

# Install Redis via PECL
RUN printf "\n" | pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Copy configuration files.
COPY ./.docker/local/php/php.ini /usr/local/etc/php/php.ini
COPY ./.docker/local/nginx/nginx.conf /etc/nginx/nginx.conf

# Configure user and group permissions
RUN if ! getent group www-data > /dev/null 2>&1; then \
        addgroup -g 1000 www-data; \
    fi && \
    if ! id -u www-data > /dev/null 2>&1; then \
        adduser -D -u 1000 -G www-data www-data; \
    else \
        usermod -u 1000 www-data 2>/dev/null || true; \
        groupmod -g 1000 www-data 2>/dev/null || true; \
    fi

# Set umask for proper file permissions
RUN echo "umask 0002" >> /etc/profile && \
    echo "umask 0002" >> /etc/bash.bashrc && \
    echo "umask 0002" >> /root/.bashrc && \
    if [ -f /home/www-data/.bashrc ]; then echo "umask 0002" >> /home/www-data/.bashrc; fi

# Set default ACL for directories to ensure new files inherit permissions
RUN setfacl -Rdm u::rwx,g::rwx,o::rx /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true && \
    setfacl -Rm u::rwx,g::rwx,o::rx /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache && \
    chown -R www-data:www-data /var/www

# Change current user to www
USER www-data

# Expose port 9000
EXPOSE 9000