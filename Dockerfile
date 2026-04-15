FROM php:8.5-fpm

# Na Debianu používáme apt-get místo apk
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalace PHP rozšíření (zůstává stejné)
RUN docker-php-ext-install \
    pdo_pgsql \
    intl \
    zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 8000