-- Script de inicialización de la base de datos
-- Este archivo se ejecuta automáticamente al crear el contenedor MySQL
-- Los archivos en /docker-entrypoint-initdb.d se ejecutan en orden alfabético

-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS sistema_compras_zapatos CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Usar la base de datos
USE sistema_compras_zapatos;

