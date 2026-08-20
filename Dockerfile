FROM php:8.2-cli-alpine

RUN apk add --no-cache git unzip
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install

CMD ["./vendor/bin/phpunit", "tests"]
