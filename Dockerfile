
FROM php:8.3-apache


RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends default-mysql-client libsnmp-dev; \
    docker-php-ext-install pdo_mysql mysqli snmp; \
    find /etc/apache2/mods-enabled -maxdepth 1 -type l -name 'mpm_*.load' -delete; \
    find /etc/apache2/mods-enabled -maxdepth 1 -type l -name 'mpm_*.conf' -delete; \
    a2enmod mpm_prefork rewrite headers; \
    test "$(find /etc/apache2/mods-enabled -maxdepth 1 -type l -name 'mpm_*.load' | wc -l)" -eq 1; \
    test -L /etc/apache2/mods-enabled/mpm_prefork.load; \
    apache2ctl -M; \
    rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && printf '%s\n' '<Directory /var/www/html>' '    AllowOverride All' '    Require all granted' '</Directory>' > /etc/apache2/conf-available/app-directory.conf \
    && a2enconf app-directory

EXPOSE 80
