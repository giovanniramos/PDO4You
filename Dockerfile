FROM php:8.2-cli-alpine

# Install build dependencies and PHP extensions, then clean up virtual packages
RUN apk add --no-cache \
        git \
        unzip \
        libpq \
        sqlite-libs \
        libzip \
    && apk add --no-cache --virtual .build-deps \
        libzip-dev \
        sqlite-dev \
        libpq-dev \
    && docker-php-ext-install \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        zip \
    && apk del .build-deps

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 1. Copy composer definitions (the wildcard prevents failures if composer.lock is omitted)
COPY composer.json composer.lock* ./

# 2. Leverage BuildKit cache mount to avoid re-downloading packages across builds
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

# 3. Copy application codebase and dump the optimized autoloader
COPY . .

RUN composer dump-autoload --optimize

CMD ["sh", "-c", "[ -f vendor/bin/phpunit ] || composer install --no-interaction --prefer-dist; ./vendor/bin/phpunit --colors=always"]
