-- ============================================================
-- SCRIPT PARA AGREGAR COLUMNA DESCUENTO A DETALLES_COMPRA
-- ============================================================
-- Este script agrega una columna para guardar si se aplicó
-- descuento del 10% a cada detalle de compra
-- ============================================================

USE sistema_compras_zapatos;

-- Agregar columna descuento (BOOLEAN/TINYINT)
ALTER TABLE `detalles_compra` 
ADD COLUMN `descuento` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 si se aplicó descuento del 10%, 0 si no';

-- ============================================================
-- VERIFICACIÓN
-- ============================================================
-- Ejecutar para verificar:
-- DESCRIBE detalles_compra;
-- ============================================================

