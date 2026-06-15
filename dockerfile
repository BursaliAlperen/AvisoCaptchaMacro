FROM php:8.2-cli

WORKDIR /app

COPY . /app/

ENV PORT=80
EXPOSE 80

CMD php -S 0.0.0.0:$PORT captcha_solver.php
