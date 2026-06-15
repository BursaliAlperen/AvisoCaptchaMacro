FROM php:8.2-apache

# Apache mod_rewrite etkinleştir
RUN a2enmod rewrite

# Çalışma dizini
WORKDIR /var/www/html

# Dosyaları kopyala
COPY . /var/www/html/

# Port
EXPOSE 80

# Apache başlat
CMD ["apache2-foreground"]
