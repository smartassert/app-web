FROM php:8.2-fpm-buster

WORKDIR /app

ARG APP_ENV=prod

ENV APP_ENV=$APP_ENV

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get -qq update && apt-get -qq -y install  \
  git \
  libzip-dev \
  zip \
  && docker-php-ext-install \
  zip \
  && apt-get autoremove -y \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY composer.json /app/
COPY bin/console /app/bin/console
COPY public/index.php public/
COPY src /app/src
COPY config/bundles.php config/services.yaml /app/config/
COPY config/packages/*.yaml /app/config/packages/
COPY config/routes.yaml /app/config/

RUN mkdir -p /app/var/log \
  && chown -R www-data:www-data /app/var/log \
  && echo "APP_SECRET=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)" > .env \
  && composer install --no-dev --no-scripts \
  && rm composer.lock \
  && php bin/console cache:clear
