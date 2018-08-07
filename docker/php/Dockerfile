FROM php:7.2-fpm

RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip curl && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN pecl install mongodb &&\
    echo "extension=mongodb.so" >> /usr/local/etc/php/conf.d/mongodb.ini

WORKDIR /var/www