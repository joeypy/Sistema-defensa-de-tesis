-- ============================================================
-- SCRIPT PARA ELIMINAR TABLA MARCAS Y SUS RELACIONES
-- ============================================================
-- Este script elimina:
-- 1. La columna marca_id de la tabla productos
-- 2. La columna marca_id de la tabla detalles_compra
-- 3. La tabla marcas completa
-- 4. Todas las relaciones (Foreign Keys) asociadas
-- ============================================================

USE sistema_admin;

-- Paso 1: Eliminar la Foreign Key de productos.marca_id
ALTER TABLE `productos` 
DROP FOREIGN KEY IF EXISTS `productos_ibfk_2`;

-- Paso 2: Eliminar el índice de productos.marca_id
ALTER TABLE `productos` 
DROP INDEX IF EXISTS `marca_id`;

-- Paso 3: Eliminar la columna marca_id de productos
ALTER TABLE `productos` 
DROP COLUMN IF EXISTS `marca_id`;

-- Paso 4: Eliminar la columna marca_id de detalles_compra
-- (No tiene Foreign Key explícita, solo referencia directa)
ALTER TABLE `detalles_compra` 
DROP COLUMN IF EXISTS `marca_id`;

-- Paso 5: Eliminar la tabla marcas completa
DROP TABLE IF EXISTS `marcas`;

-- ============================================================
-- VERIFICACIÓN
-- ============================================================
-- Ejecutar estas consultas para verificar:
-- SHOW TABLES LIKE 'marcas';
-- DESCRIBE productos;
-- DESCRIBE detalles_compra;
-- ============================================================

