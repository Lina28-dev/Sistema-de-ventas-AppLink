# Usa una imagen base de PHP con Apache (te da el servidor web)
FROM php:8.2-apache

# Habilita el módulo de reescritura de URLs (útil para frameworks y URLs limpias)
RUN a2enmod rewrite

# Instala Composer (el gestor de dependencias de PHP)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copia todos tus archivos de código PHP al directorio web de Apache
COPY . /var/www/html/

# Si usas Composer, descomenta la siguiente línea para instalar dependencias
# RUN composer install --no-dev --optimize-autoloader

# El servidor Apache ya está configurado para ejecutarse por defecto
