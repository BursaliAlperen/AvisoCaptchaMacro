FROM php:8.2-apache

# Apache ayarları
RUN a2enmod rewrite

WORKDIR /var/www/html

# Tüm dosyaları kopyala
COPY . /var/www/html/

# Apache portunu Render'ın $PORT'una ayarla
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Apache'i ön planda çalıştır
CMD ["apache2-foreground"]
