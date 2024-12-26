# Dockerfile
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y default-mysql-client cron

RUN docker-php-ext-install pdo pdo_mysql

RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

COPY crontab /etc/cron.d/my-cron-job

RUN chmod 0644 /etc/cron.d/my-cron-job

RUN crontab /etc/cron.d/my-cron-job

RUN mkdir /var/log/cron

CMD ["sh", "-c", "cron && php-fpm"]

RUN sudo docker-compose up -d
RUN sudo docker exec laravel_0_app_1 composer install
RUN sudo docker exec laravel_0_app_1 php artisan migrate
RUN sudo docker exec laravel_0_app_1 php artisan serve --host=0.0.0.0 --port=9001