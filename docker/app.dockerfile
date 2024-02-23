FROM php:8.3-apache

RUN apt-get update && apt-get install -y
RUN apt-get install supervisor zip unzip -y

RUN a2enmod rewrite
RUN a2enmod expires
RUN a2enmod headers

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions gd xdebug pdo_mysql zip mongodb intl opcache redis bcmath pcntl

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf

CMD supervisord --nodaemon
