FROM tabenguyen/eccube4-apache:1.5

WORKDIR /var/www/html

RUN php bin/console eccube:plugin:generate TPS TPS 1.0.0

EXPOSE 80 443