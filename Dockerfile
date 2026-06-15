FROM php:8.2-apache

# Apache rewrite modülünü aç
RUN a2enmod rewrite

# AllowOverride All yap (.htaccess çalışsın)
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Dosyaları kopyala
COPY . /var/www/html/

WORKDIR /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
