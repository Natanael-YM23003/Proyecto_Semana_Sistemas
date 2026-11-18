FROM php:8.4-apache

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_HOME=/var/www/.composer \
    COMPOSER_ALLOW_SUPERUSER=1

# ---------------------------------------------------------------------------
# Dependencias del sistema y extensiones de PHP
# ---------------------------------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        gnupg \
        unzip \
        zlib1g-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_pgsql \
        pgsql \
        zip \
    && rm -rf /var/lib/apt/lists/*

# ---------------------------------------------------------------------------
# Node.js 24 (NodeSource) y Composer 2
# ---------------------------------------------------------------------------
RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php \
    && php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm /tmp/composer-setup.php \
    && composer --version

# ---------------------------------------------------------------------------
# www-data toma el UID/GID del usuario de Ubuntu (HOST_UID / HOST_GID)
# Así Composer, Artisan y npm crean archivos a nombre del usuario del host
# y Apache puede escribir en storage/.
# ---------------------------------------------------------------------------
ARG HOST_UID=1000
ARG HOST_GID=1000

RUN groupmod -o -g "${HOST_GID}" www-data \
    && usermod -o -u "${HOST_UID}" -g "${HOST_GID}" www-data \
    && chown -R www-data:www-data /var/www

# ---------------------------------------------------------------------------
# Apache: multiproyecto por subdominio (mod_vhost_alias)
# ---------------------------------------------------------------------------
RUN a2enmod rewrite headers expires vhost_alias

COPY docker/apache/sites.conf /etc/apache2/sites-available/laravel.conf
COPY docker/inicio /var/www/inicio
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

RUN printf 'ServerName localhost\n' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && a2dissite 000-default \
    && a2ensite laravel \
    && chown -R www-data:www-data /var/www

WORKDIR /var/www/html

EXPOSE 80 5173
