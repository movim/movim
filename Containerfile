FROM serversideup/php:8.4-fpm-nginx-alpine

USER root

# S6
COPY ./etc/s6-overlay/s6-rc.d/movim-migrations/ /etc/s6-overlay/s6-rc.d/movim-migrations/
COPY ./etc/s6-overlay/s6-rc.d/movim-daemon/ /etc/s6-overlay/s6-rc.d/movim-daemon/
RUN touch /etc/s6-overlay/s6-rc.d/user/contents.d/movim-migrations
RUN touch /etc/s6-overlay/s6-rc.d/user/contents.d/movim-daemon

# nginx websocket
COPY /etc/nginx/conf.d/movim-websocket.conf /etc/nginx/server-opts.d/

# PHP
RUN install-php-extensions imagick gd

# Movim

ENV SSL_MODE=full
ENV PHP_OPCACHE_ENABLE=1
ENV DAEMON_INTERFACE=127.0.0.1
ENV DAEMON_PORT=8083
ENV NGINX_WEBROOT=/var/www/html/public

ADD . /var/www/html
WORKDIR /var/www/html

RUN cd /var/www/html
RUN composer install
# Ensure that the host .env is not copied
RUN rm -rf /var/www/.env
# Setup some directories
RUN rm -rf cache; \
    mkdir cache; \
    chown -R www-data:www-data cache; \
    rm -rf public/cache; \
    mkdir public/cache; \
    chown -R www-data:www-data public/cache; \
    rm -rf log; \
    mkdir log; \
    chown -R www-data:www-data log/

USER www-data