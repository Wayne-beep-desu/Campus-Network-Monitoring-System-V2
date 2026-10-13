
FROM php:8.3-apache

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        default-mysql-client \
        libsnmp-dev; \
    docker-php-ext-install pdo_mysql mysqli snmp; \
    a2dismod mpm_event mpm_worker || true; \
    a2dismod mpm_prefork || true; \
    rm -f /etc/apache2/mods-enabled/mpm_*.load \
          /etc/apache2/mods-enabled/mpm_*.conf; \
    a2enmod mpm_prefork rewrite headers; \
    apache2ctl -M; \
    rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html

RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && printf '%s\n' \
       '<Directory /var/www/html>' \
       '    AllowOverride All' \
       '    Require all granted' \
       '</Directory>' \
       > /etc/apache2/conf-available/app-directory.conf \
    && a2enconf app-directory

EXPOSE 80

CMD ["apache2-foreground"]
