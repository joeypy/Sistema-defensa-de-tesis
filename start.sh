#!/bin/bash

# Script para iniciar el proyecto con Docker

echo "🚀 Iniciando Sistema de Gestión de Compras..."
echo ""

# Verificar si Docker está instalado
if ! command -v docker &> /dev/null; then
    echo "❌ Docker no está instalado. Por favor instala Docker primero."
    exit 1
fi

# Verificar si Docker Compose está instalado
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose no está instalado. Por favor instala Docker Compose primero."
    exit 1
fi

# Construir y levantar los contenedores
echo "📦 Construyendo imágenes Docker..."
docker-compose build

echo ""
echo "🔧 Iniciando contenedores..."
docker-compose up -d

echo ""
echo "⏳ Esperando a que los servicios estén listos..."
sleep 10

echo ""
echo "✅ ¡Servicios iniciados correctamente!"
echo ""
echo "📍 Accesos:"
echo "   - Aplicación web: http://localhost:8082"
echo "   - phpMyAdmin: http://localhost:8081"
echo ""
echo "🔑 Credenciales por defecto:"
echo "   - Usuario: admin"
echo "   - Contraseña: admin123"
echo ""
echo "📊 Base de datos:"
echo "   - Host: localhost:3306"
echo "   - Usuario: root"
echo "   - Contraseña: rootpassword"
echo "   - Base de datos: sistema_compras_zapatos"
echo ""
echo "Para ver los logs: docker-compose logs -f"
echo "Para detener: docker-compose down"

