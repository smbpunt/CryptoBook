ARG PHP_VERSION=8.5

FROM php:${PHP_VERSION}-fpm-alpine

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apk add --no-cache git unzip \
    && install-php-extensions intl opcache pdo_pgsql zip

# Symfony Language Tools (beta), run by `make ci`. Static binary: same archive on glibc and musl.
ARG SYMFONY_LSP_VERSION=0.24.2
ARG SYMFONY_LSP_SHA256_ARM64=61ab69f7dbfe2a3216584fdde9e2247e56085461796f96fa1e14711dcb729400
ARG SYMFONY_LSP_SHA256_X64=d7e39420ad9058fb1edeaf13f0846c5e7c351467b90702336c12b8ce13f8a09f

RUN set -eux; \
    case "$(uname -m)" in \
        aarch64) arch=arm64; sha="$SYMFONY_LSP_SHA256_ARM64" ;; \
        x86_64) arch=x64; sha="$SYMFONY_LSP_SHA256_X64" ;; \
        *) echo "Unsupported architecture: $(uname -m)"; exit 1 ;; \
    esac; \
    name="symfony-lsp-v${SYMFONY_LSP_VERSION}-linux-${arch}"; \
    curl -sSfL -o /tmp/symfony-lsp.tar.gz "https://github.com/symfony/language-tools/releases/download/v${SYMFONY_LSP_VERSION}/${name}.tar.gz"; \
    echo "${sha}  /tmp/symfony-lsp.tar.gz" | sha256sum -c -; \
    tar -xzf /tmp/symfony-lsp.tar.gz -C /tmp; \
    mv "/tmp/${name}/symfony-lsp" /usr/local/bin/symfony-lsp; \
    rm -rf /tmp/symfony-lsp.tar.gz "/tmp/${name}"; \
    symfony-lsp --version

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY docker/php/conf.d/app.ini $PHP_INI_DIR/conf.d/zz-app.ini

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app
