# Dockerfile
FROM php:8.2-fpm

# Установка необходимых расширений
RUN apt-get update && apt-get install -y default-mysql-client cron

# Установка расширений PHP
RUN docker-php-ext-install pdo pdo_mysql

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# Установите зависимости Laravel
RUN composer install

# Убедитесь, что права на папку storage и bootstrap/cache корректные
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Копируйте файл crontab в контейнер
COPY crontab /etc/cron.d/my-cron-job

# Убедитесь, что файл cron имеет правильные разрешения
RUN chmod 0644 /etc/cron.d/my-cron-job

# Примените cron задания
RUN crontab /etc/cron.d/my-cron-job

# Создайте директорию для журналов
RUN mkdir /var/log/cron

# Запустите cron и php-fpm
CMD ["sh", "-c", "cron && php-fpm"]
