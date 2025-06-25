FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
         libzip-dev \
         zip \
         procps \
         libpng-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-install gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pcntl

RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && apt-get clean -y

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# https://getcomposer.org/doc/03-cli.md#environment-variables
ENV COMPOSER_HOME /composer
ENV COMPOSER_PROCESS_TIMEOUT 45
ENV COMPOSER_NO_INTERACTION 1
ENV COMPOSER_DISABLE_XDEBUG_WARN 1
ENV COMPOSER_ALLOW_SUPERUSER 1

# https://github.com/boot2docker/boot2docker/issues/587#issuecomment-114868208
RUN usermod -u ${DOCKER_USER_ID:-1000} www-data

# forward php error logs to docker log collector
# RUN ln -sf /dev/stderr /var/www/html/storage/logs/php.log

VOLUME ["/var/www/html"]
