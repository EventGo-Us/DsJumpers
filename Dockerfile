################################################################################
# DsJumpers: nginx + php-fpm in a single container (Alpine)
#
# Stages:
#   vendor  - installs Composer dependencies from composer.lock
#   runtime - php-fpm + nginx, runs as www-data, listens on 8080
#
# Config is NOT baked into the image. At startup the entrypoint writes
# /var/www/html/.env from the env vars ECS injects from SSM (see
# docker/write-dotenv.php).
################################################################################

ARG PHP_VERSION=8.3

# ------------------------------------------------------------------------------
# Stage 1: Composer dependencies
# ------------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

# Only the manifest first, so this layer stays cached until dependencies change.
COPY composer.json composer.lock ./

# Platform reqs are checked against the runtime stage instead (the composer
# image ships its own PHP version); composer.lock pins the exact versions.
RUN composer install \
      --no-dev \
      --no-interaction \
      --no-progress \
      --prefer-dist \
      --no-scripts \
      --optimize-autoloader \
      --ignore-platform-reqs

# ------------------------------------------------------------------------------
# Stage 2: Runtime
# ------------------------------------------------------------------------------
FROM php:${PHP_VERSION}-fpm-alpine AS runtime

# Extension installer: resolves the Alpine build/runtime deps of each extension
# and removes the build deps afterwards. gd is built with AVIF/WebP/JPEG/PNG.
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions gd pdo_mysql opcache \
 && rm /usr/local/bin/install-php-extensions \
 && apk add --no-cache nginx tini \
 && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 # The image's default pool config is replaced by docker/php/fpm-pool.conf
 && rm -f /usr/local/etc/php-fpm.d/www.conf.default /usr/local/etc/php-fpm.d/zz-docker.conf

# Server config (changes rarely, so it sits above the app code layers).
COPY docker/php/php.ini        $PHP_INI_DIR/conf.d/zz-app.ini
COPY docker/php/fpm-pool.conf  /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx/nginx.conf        /etc/nginx/nginx.conf
COPY docker/nginx/php_fastcgi.conf  /etc/nginx/php_fastcgi.conf
COPY docker/entrypoint.sh                /usr/local/bin/docker-entrypoint.sh
COPY docker/write-dotenv.php              /usr/local/bin/write-dotenv.php

WORKDIR /var/www/html

# Dependencies before app code: a code-only change reuses this layer.
COPY --from=vendor /app/vendor ./vendor

# App code is owned by root and read-only for www-data.
COPY . .

# Writable paths for www-data:
# - .env and the Google service account file, written by the entrypoint
# - ajax/tmp: image uploads before they are pushed to R2
# - dompdf font cache
# - nginx/php runtime dirs
RUN set -eux; \
    chmod 0755 /usr/local/bin/docker-entrypoint.sh; \
    touch .env ajax/gaxi-487815-79b122d9b0f0.json; \
    chown www-data:www-data .env ajax/gaxi-487815-79b122d9b0f0.json; \
    chmod 0600 .env ajax/gaxi-487815-79b122d9b0f0.json; \
    mkdir -p ajax/tmp/evidencias ajax/tmp/events; \
    chown -R www-data:www-data ajax/tmp vendor/dompdf/dompdf/lib/fonts; \
    mkdir -p /tmp/nginx /tmp/php-sessions; \
    chown -R www-data:www-data /tmp/nginx /tmp/php-sessions /var/lib/nginx /var/log/nginx /run/nginx 2>/dev/null || true

# Sized for a 1 GB task (~50 MB per worker). Override per task if needed.
ENV PHP_FPM_MAX_CHILDREN=16

USER www-data

EXPOSE 8080

# The php-fpm base image sets SIGQUIT; the entrypoint handles TERM (what ECS sends)
STOPSIGNAL SIGTERM

# -s: on ECS with exec enabled, the platform init is PID 1, so tini registers
# as a subreaper to keep reaping zombies.
ENTRYPOINT ["/sbin/tini", "-s", "--", "docker-entrypoint.sh"]
