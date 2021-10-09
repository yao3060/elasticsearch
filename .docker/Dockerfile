FROM nginx:1.19-alpine as nginx
# copy NGINX configuration to image
COPY ./.docker/nginx/etc/nginx/cert /etc/nginx/cert
COPY ./.docker/nginx/etc/nginx/passwd /etc/nginx/passwd
COPY ./.docker/nginx/etc/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY ./.docker/nginx/etc/nginx/snippets/cors /etc/nginx/snippets/cors

# copy src/public folder to NGINX
COPY ./src/web /var/www/html/web

# set folder permissions
RUN find /var/www/html -type d -exec chmod 755 {} \;

FROM yao3060/yii2-php:8-fpm-alpine as php

# copy PHP configuration to image
COPY .docker/php/usr/local/etc/php/ /usr/local/etc/php/
COPY .docker/php/usr/local/etc/php-fpm.d/ /usr/local/etc/php-fpm.d/

# copy source code to image
COPY ./src /var/www/html

# # Run this after copy source to image instead of /start
RUN chown -R :www-data /var/www/html/web && \
    mkdir -p /var/www/html/runtime && \
    mkdir -p /var/www/html/runtime/debug && \
    mkdir -p /var/www/html/runtime/logs && \
    mkdir -p /var/www/html/runtime/cache && \
    chmod -R 777 /var/www/html/runtime && \
    chown -R www-data:www-data /var/www/html/runtime && \
    chmod +x /var/www/html/yii

# copy start script to image
COPY .docker/php/start /start
COPY .docker/php/init /init

# run start script on container startup
CMD ["/start"]
