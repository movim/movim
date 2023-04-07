# this file is borrowed from https://github.com/movim/movim_docker

FROM php:8.1-fpm

RUN set -ex; \
	\
	apt-get update; \
	apt-get install -qq --no-install-suggests --no-install-recommends \
		unzip \
	; \
	\
	savedAptMark="$(apt-mark showmanual)"; \
	\
	apt-get install -qq --no-install-suggests --no-install-recommends \
		libmagickwand-dev \
		libjpeg-dev \
		libpng-dev \
		libwebp-dev \
		libpq-dev \
		libzip-dev \
	; \
	\
	docker-php-ext-configure gd --with-jpeg=/usr --with-webp=/usr; \
	docker-php-ext-install gd pdo_pgsql pgsql zip; \
	\
	pecl install imagick-3.7.0; \
	docker-php-ext-enable imagick; \
	\
	apt-mark auto '.*' > /dev/null; \
	apt-mark manual $savedAptMark; \
	ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
		| awk '/=>/ { print $3 }' \
		| sort -u \
		| xargs -r dpkg-query -S \
		| cut -d: -f1 \
		| sort -u \
		| xargs -rt apt-mark manual; \
	\
	apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
	rm -rf /var/lib/apt/lists/*

RUN mkdir -p /usr/src/movim && chown -R www-data:www-data /usr/src/movim

WORKDIR /usr/src/movim

COPY . .

RUN curl -sS https://getcomposer.org/installer | php \
    && php composer.phar install --optimize-autoloader

WORKDIR /var/www/html

COPY docker-entrypoint.sh /usr/local/bin/
ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["su", "-s", "/bin/sh", "-c", "php daemon.php start", "www-data"]
