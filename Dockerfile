FROM php:8.2-apache

# Apache mod_rewrite etkinleştir
RUN a2enmod rewrite

# Çalışma dizini
WORKDIR /var/www/html

# Dosyaları kopyala
COPY . /var/www/html/

# Apache portunu dinamik yap (entrypoint script ile)
RUN echo '#!/bin/bash\n\n# Render'ın verdiği PORT'u kullan (varsayılan 80)\nPORT=${PORT:-80}\n\n# Apache config'lerini güncelle\nsed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf\nsed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf\n\n# Apache'i başlat\napache2-foreground' > /entrypoint.sh && chmod +x /entrypoint.sh

# Port expose et
EXPOSE 80

# Entrypoint çalıştır
CMD ["/entrypoint.sh"]
