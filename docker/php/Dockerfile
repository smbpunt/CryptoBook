FROM php:8.1-fpm-alpine as php

# Copy the php config file
#COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

# Symfony CLI
RUN  apk add --no-cache bash && curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | bash &&  apk add symfony-cli

# bonus
RUN  apk add --no-cache git openssh


# Copy the application code
COPY . /app

WORKDIR /app
VOLUME ["/app"]
