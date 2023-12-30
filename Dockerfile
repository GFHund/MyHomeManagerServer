FROM php:8.2.14-fpm-bullseye

RUN apt-get update && \
apt-get install -y zip unzip libicu-dev vim less cron && \
docker-php-ext-configure intl &&\
docker-php-ext-configure mysqli &&\
docker-php-ext-configure pdo_mysql &&\
docker-php-ext-install intl &&\
docker-php-ext-install mysqli &&\
docker-php-ext-install pdo_mysql &&\
docker-php-ext-enable mysqli

RUN ls /usr/src

COPY . /usr/src
# Arbeitsverzeichnis wird gesetzt
WORKDIR /usr/src

# RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
php composer-setup.php &&\
php -r "unlink('composer-setup.php');" &&\
php composer.phar update &&\
php composer.phar install

#RUN vendor/bin/phpunit

#RUN composer update

# Abhängigkeiten werden mittels Composer installiert
#RUN composer install


# create the database
#RUN php bin/console doctrine:database:create
#RUN php bin/console make:migration
#RUN php bin/console doctrine:migrations:migrate
