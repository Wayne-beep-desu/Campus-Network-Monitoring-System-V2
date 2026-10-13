FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends default-mysql-client libsnmp-dev \
    && docker-php-ext-install pdo_mysql mysqli \
    && docker-php-ext-install snmp \
    && a2dismod mpm_event mpm_worker \
    && a2enmod mpm_prefork rewrite headers \
    && rm -rf /var/lib/apt/lists/*

RUN apache2ctl -M 2>&1 | grep -E 'mpm_(event|worker|prefork)_module' \
    && test "$(apache2ctl -M 2>&1 | grep -Ec 'mpm_(event|worker|prefork)_module')" -eq 1

ENV APACHE_DOCUMENT_ROOT=/var/www/html

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && printf '%s\n' '<Directory /var/www/html>' '    AllowOverride All' '    Require all granted' '</Directory>' > /etc/apache2/conf-available/app-directory.conf \
    && a2enconf app-directory

EXPOSE 80
