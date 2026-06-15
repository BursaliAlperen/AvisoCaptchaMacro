FROM php:8.2-apache

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html/

# Render PORT env variable'ını kullan
ENV PORT=80
EXPOSE 80

# Apache'i PORT env variable ile başlat
CMD sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf && \
    sed -i "s/:80/:${PORT}/" /etc/apache2/sites-enabled/000-default.conf && \
    apache2-foreground
