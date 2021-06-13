FROM bitnami/php-fpm:7.4

RUN apt-get update && apt-get install -y zip unzip

RUN ls /app

COPY . /app/
# Arbeitsverzeichnis wird gesetzt
WORKDIR /app/

RUN composer update

# Abhängigkeiten werden mittels Composer installiert
RUN composer install


# create the database
#RUN php bin/console doctrine:database:create 
#RUN php bin/console make:migration
#RUN php bin/console doctrine:migrations:migrate