FROM php:8.2-apache

RUN apt-get update && apt-get upgrade -y \
    && apt-get install -y libmariadb-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY ./ /var/www/html
