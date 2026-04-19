FROM php:8.2-apache

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    git unzip curl zip libzip-dev libpng-dev \
    && docker-php-ext-install pdo_mysql zip

# Habilitar rewrite
RUN a2enmod rewrite

# Directorio
WORKDIR /var/www/html

# Instalar Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Configurar Apache para Laravel
RUN sed -i 's|/var/www/html|/var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf

EXPOSE 80