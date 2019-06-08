FROM tabenguyen/eccube4-apache:1.5

WORKDIR /var/www/html

RUN php bin/console eccube:plugin:generate SNS_Login SNS_Login 1.0.0

EXPOSE 80 443