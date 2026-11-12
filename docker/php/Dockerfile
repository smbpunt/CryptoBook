ARG PHP_VERSION=8.5

FROM php:${PHP_VERSION}-fpm-alpine

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apk add --no-cache git unzip \
    && install-php-extensions intl opcache pdo_pgsql zip

# Symfony Language Tools (beta), run by `make ci`. Version pinned in the script, shared with the GitLab CI.
COPY docker/php/install-symfony-lsp.sh /usr/local/bin/install-symfony-lsp
RUN install-symfony-lsp

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY docker/php/conf.d/app.ini $PHP_INI_DIR/conf.d/zz-app.ini

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app
