FROM php:8.2-cli-alpine

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-install pcntl pdo_mysql

WORKDIR /www

COPY . .

RUN composer install --no-dev --no-interaction --optimize-autoloader

CMD ["php", "puff", "run"]
