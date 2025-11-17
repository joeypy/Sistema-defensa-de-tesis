-- ============================================================
-- SCRIPT SIMPLE PARA ELIMINAR MARCAS DEL SISTEMA
-- Compatible con phpMyAdmin
-- ============================================================
-- Este script elimina:
-- 1. Columnas marca_id de las tablas
-- 2. La tabla marcas completa
-- ============================================================
-- INSTRUCCIONES:
-- Ejecuta este script completo. Si alguna sentencia falla porque 
-- el elemento no existe, simplemente continúa con la siguiente.
-- ============================================================

USE sistema_compras_zapatos;

-- ============================================================
-- PASO 1: Eliminar columnas marca_id de las tablas
-- ============================================================
-- Nota: Si alguna columna no existe, la sentencia fallará pero puedes continuar

-- Eliminar marca_id de productos (si existe)
ALTER TABLE productos DROP COLUMN marca_id;

-- Eliminar marca_id de detalles_compra (si existe)
ALTER TABLE detalles_compra DROP COLUMN marca_id;

-- Eliminar marca_id de historial (si existe)
ALTER TABLE historial DROP COLUMN marca_id;

-- ============================================================
-- PASO 2: Eliminar la tabla marcas
-- ============================================================
DROP TABLE IF EXISTS marcas;

-- ============================================================
-- FIN DEL SCRIPT
-- ============================================================
-- Si necesitas verificar qué columnas relacionadas con marcas existen,
-- ejecuta esta consulta:
-- 
-- SELECT TABLE_NAME, COLUMN_NAME
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = 'sistema_compras_zapatos' 
-- AND COLUMN_NAME LIKE '%marca%';
-- ============================================================
