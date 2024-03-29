FROM php:8.3-fpm-bookworm

WORKDIR /app

ARG APP_ENV=prod
ARG DATABASE_URL=postgresql://database_user:database_password@0.0.0.0:5432/database_name?serverVersion=12&charset=utf8
ARG API_CLIENT_BASE_URL=https://api-client/
ARG PRIMARY_TOKEN_ENCRYPTION_KEY=primary_token_encryption_key
ARG SECONDARY_TOKEN_ENCRYPTION_KEY=primary_token_encryption_key

ENV APP_ENV=$APP_ENV
ENV DATABASE_URL=$DATABASE_URL
ENV API_CLIENT_BASE_URL=$API_CLIENT_BASE_URL
ENV PRIMARY_TOKEN_ENCRYPTION_KEY=$PRIMARY_TOKEN_ENCRYPTION_KEY
ENV SECONDARY_TOKEN_ENCRYPTION_KEY=$SECONDARY_TOKEN_ENCRYPTION_KEY

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get -qq update && apt-get -qq -y install  \
  git \
  libpq-dev \
  libzip-dev \
  zip \
  && docker-php-ext-install \
  pdo_pgsql \
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
COPY migrations /app/migrations
COPY templates /app/templates

RUN mkdir -p /app/var/log \
  && chown -R www-data:www-data /app/var/log \
  && echo "APP_SECRET=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)" > .env \
  && COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-scripts \
  && rm composer.lock \
  && php bin/console cache:clear
