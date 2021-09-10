FROM nginx:1.19-alpine as nginx
# copy NGINX configuration to image
COPY ./.docker/nginx/etc/nginx/cert /etc/nginx/cert
COPY ./.docker/nginx/etc/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY ./.docker/nginx/etc/nginx/snippets/cors /etc/nginx/snippets/cors

# copy src/public folder to NGINX
COPY ./src/web /var/www/html/web

# set folder permissions
RUN find /var/www/html -type d -exec chmod 755 {} \;



FROM composer:2 as composer

# copy source code to image
COPY ./src/composer.json ./src/composer.lock /app/
RUN composer install --no-scripts --no-autoloader --prefer-dist -vvv


FROM yao3060/yii2-php:8-fpm-alpine as php

# copy PHP configuration to image
COPY .docker/php/usr/local/etc/php/ /usr/local/etc/php/
COPY .docker/php/usr/local/etc/php-fpm.d/ /usr/local/etc/php-fpm.d/

# copy source code to image
COPY --from=composer /app/vendor /var/www/html/vendor
COPY --from=composer /usr/bin/composer /usr/bin/composer

# copy source code to image
COPY ./src /var/www/html

RUN /usr/bin/composer dump-autoload

# # Run this after copy source to image instead of /start
RUN chown -R :www-data /var/www/html/web

# copy start script to image
COPY .docker/php/start /start
COPY .docker/php/init /init

# run start script on container startup
CMD ["/start"]
