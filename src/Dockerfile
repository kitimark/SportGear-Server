FROM php:7.3.6-fpm

ENV APP_ROOT /app

RUN mkdir ${APP_ROOT}
WORKDIR ${APP_ROOT}
ADD . ${APP_ROOT}

RUN apt-get update && apt-get install -y libpng-dev
RUN docker-php-ext-install pdo_mysql mbstring

RUN chown -R www-data:www-data /app/logs

EXPOSE 9000
CMD ["php-fpm"]