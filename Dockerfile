### Extensions
FROM php:7.3-apache as app

LABEL stage=intermediate

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        libcurl4-gnutls-dev \
        libpng-dev

WORKDIR /app

RUN docker-php-ext-install \
    curl \
    opcache \
    gd \
    json

RUN echo '\
opcache.enable=1\n\
opcache.memory_consumption=256\n\
opcache.interned_strings_buffer=16\n\
opcache.max_accelerated_files=16000\n\
opcache.validate_timestamps=0\n\
opcache.load_comments=Off\n\
opcache.save_comments=1\n\
opcache.fast_shutdown=0\n\
' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

COPY / /app

### Final Image
FROM php:7.3-apache

COPY --from=app /app /opt/app
COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf

COPY --from=app /usr/local/etc/php/conf.d/*.ini /usr/local/etc/php/conf.d/
COPY --from=app /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/

RUN apt-get update && apt-get install -y libpng-dev 
RUN apt-get install -y \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libpng-dev libxpm-dev \
    libfreetype6-dev

RUN docker-php-ext-configure gd \
    --with-gd \
    --with-webp-dir \
    --with-jpeg-dir \
    --with-png-dir \
    --with-zlib-dir \
    --with-xpm-dir \
    --with-freetype-dir

RUN docker-php-ext-install gd

RUN chown -R www-data:www-data /opt/app && \
    a2enmod rewrite
