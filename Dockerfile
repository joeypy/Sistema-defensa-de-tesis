FROM php:8.2-apache

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Instalar herramientas adicionales
RUN apt-get update && apt-get install -y \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Configurar el directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de la aplicación
COPY . /var/www/html/

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configurar Apache para servir desde la raíz
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Script de inicio que configura el puerto dinámicamente para Railway
# Railway asigna un puerto dinámico en la variable PORT
RUN echo '#!/bin/bash\n\
set -e\n\
# Railway asigna un puerto dinámico en la variable PORT\n\
# Necesitamos configurar Apache para usar ese puerto si está disponible\n\
if [ -n "$PORT" ] && [ "$PORT" != "80" ]; then\n\
    echo "Configurando Apache para usar puerto $PORT"\n\
    sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf\n\
    sed -i "s/*:80/*:$PORT/g" /etc/apache2/sites-available/000-default.conf\n\
else\n\
    echo "Usando puerto por defecto 80"\n\
fi\n\
exec apache2-foreground' > /usr/local/bin/start-apache.sh && \
    chmod +x /usr/local/bin/start-apache.sh

# Exponer el puerto 80 (Railway mapeará su puerto dinámico a este)
EXPOSE 80

# Comando por defecto
CMD ["/usr/local/bin/start-apache.sh"]

