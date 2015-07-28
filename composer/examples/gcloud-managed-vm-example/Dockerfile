FROM php:5.6-apache

# To use encryption (for ex. cipher_key) mrypt php extension should be installed
RUN apt-get update && apt-get install -y \
        libmcrypt-dev

RUN docker-php-ext-install mcrypt

COPY ./src/ /var/www/html/
COPY ./config/apache2.conf /etc/apache2/
RUN rm /etc/apache2/sites-available/*
COPY ./config/pubnub.conf /etc/apache2/sites-available/
RUN ln -s /etc/apache2/sites-available/pubnub.conf /etc/apache2/sites-enabled/

# TBD: setup correct apache logs path
RUN mkdir -p /var/log/app_engine/custom_logs
RUN chmod -R 777 /var/log/app_engine/custom_logs/


EXPOSE 8080
