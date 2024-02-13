FROM php:7.4-apache

RUN apt-get update
RUN apt-get install -y autoconf pkg-config libssl-dev
RUN apt-get install -y sendmail libpng-dev

RUN apt-get update && \
    apt-get install -y \
        zlib1g-dev

RUN apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev git zip unzip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-configure gd
RUN docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-install -j$(nproc) iconv
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install opcache

RUN pecl install xdebug-3.1.5 \
    && docker-php-ext-enable xdebug

COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN a2enmod rewrite
RUN a2enmod ssl

RUN usermod -u 1000 www-data
RUN groupmod -g 1000 www-data

