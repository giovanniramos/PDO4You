FROM php:8.2-cli-alpine

RUN apk add --no-cache git unzip libzip-dev sqlite-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install

CMD ["./vendor/bin/phpunit", "tests"]
