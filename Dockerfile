# Laravel 13 requires PHP >= 8.3; symfony 8.x (in the lock) requires >= 8.4.
# Pin to 8.4-alpine — stable, widely available, and satisfies the lock.
FROM php:8.4-fpm-alpine

# Avoid dl-cdn TLS/transient failures during apk
RUN sed -i 's|dl-cdn.alpinelinux.org|mirror.leaseweb.com|g' /etc/apk/repositories

# System dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    git \
    curl \
    netcat-openbsd \
    postgresql-client \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    icu-dev \
    nodejs \
    npm \
    ca-certificates \
    tzdata

# Timezone
ENV TZ=Africa/Johannesburg
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# PHP extensions (install-php-extensions handles build deps + cleanup)
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    pdo_pgsql \
    mbstring \
    pcntl \
    bcmath \
    gd \
    exif \
    zip \
    intl \
    opcache

# phpredis from GitHub source (PECL is unreliable)
ENV PHPREDIS_VERSION=6.3.0
RUN set -eux; \
    apk add --no-cache --virtual .phpredis-build-deps $PHPIZE_DEPS; \
    curl -fsSL "https://github.com/phpredis/phpredis/archive/refs/tags/${PHPREDIS_VERSION}.tar.gz" -o /tmp/phpredis.tar.gz; \
    mkdir -p /tmp/phpredis; \
    tar -xzf /tmp/phpredis.tar.gz -C /tmp/phpredis --strip-components=1; \
    cd /tmp/phpredis; \
    phpize; \
    ./configure; \
    make -j"$(nproc)"; \
    make install; \
    docker-php-ext-enable redis; \
    cd /; \
    rm -rf /tmp/phpredis /tmp/phpredis.tar.gz; \
    apk del .phpredis-build-deps

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application
COPY . .

# PHP dependencies (respect the committed lock)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Publish Filament assets, then build front-end assets
RUN php artisan filament:assets \
    && npm install --no-audit --no-fund && npm run build && rm -rf node_modules

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

RUN mkdir -p /var/log/supervisor /run/nginx /var/log/php

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
