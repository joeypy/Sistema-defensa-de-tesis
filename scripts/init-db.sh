#!/bin/bash
# Script para inicializar la base de datos en producción
# Este script puede ejecutarse manualmente después del despliegue

set -e

echo "Inicializando base de datos..."

# Variables de entorno (deben estar configuradas)
DB_HOST=${DB_HOST:-localhost}
DB_NAME=${DB_NAME:-sistema_compras_zapatos}
DB_USER=${DB_USER:-root}
DB_PASS=${DB_PASS:-}

# Esperar a que MySQL esté listo
echo "Esperando a que MySQL esté disponible..."
until mysqladmin ping -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" --silent; do
    echo "MySQL no está disponible aún, esperando..."
    sleep 2
done

echo "MySQL está disponible. Ejecutando scripts SQL..."

# Ejecutar scripts en orden
if [ -f "/var/www/html/database/init.sql" ]; then
    echo "Ejecutando init.sql..."
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" < /var/www/html/database/init.sql
fi

if [ -f "/var/www/html/database/01-schema.sql" ]; then
    echo "Ejecutando 01-schema.sql..."
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < /var/www/html/database/01-schema.sql
fi

if [ -f "/var/www/html/database/02-data.sql" ]; then
    echo "Ejecutando 02-data.sql..."
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < /var/www/html/database/02-data.sql
fi

echo "Base de datos inicializada correctamente."

