FROM php:8.1-fpm-alpine3.16

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions @composer gd curl xml zip bcmath mbstring intl redis sqlite3

RUN apk add --no-cache zip git curl bash

RUN mkdir -p /app \
    && addgroup -g 1000 app \
    && adduser -u 1000 -G app -h /app -s /bin/sh -D app \
    && chown -R app:app /app

WORKDIR /app

COPY --chown=app:app . /app

USER app

# RUN composer install --no-dev

CMD ["composer", "run", "test"]
