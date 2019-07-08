FROM php:7.3.6-fpm

RUN apt-get update && apt-get install -y libpng-dev

RUN docker-php-ext-install pdo_mysql mbstring

EXPOSE 9000
CMD ["php-fpm"]