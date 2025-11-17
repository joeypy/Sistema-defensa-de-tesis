-- ============================================================
-- SCRIPT PARA SIMPLIFICAR LA BASE DE DATOS
-- Eliminar compras y tablas no usadas, mantener solo ventas
-- Compatible con phpMyAdmin
-- ============================================================
-- Este script:
-- 1. Elimina Foreign Keys relacionadas con tablas a eliminar
-- 2. Elimina tablas de compras y no usadas
-- 3. Elimina tablas antiguas de ventas mal diseñadas
-- 4. Crea nueva estructura de ventas limpia
-- ============================================================

USE sistema_compras_zapatos;

-- ============================================================
-- PASO 1: Eliminar Foreign Keys de tablas que vamos a eliminar
-- ============================================================
-- NOTA: MySQL no soporta IF EXISTS con DROP FOREIGN KEY
-- Si alguna foreign key no existe, simplemente ignora el error y continúa

-- Eliminar FK de ventas (antigua) relacionada con facturas
-- Si alguna no existe, ignora el error
ALTER TABLE ventas DROP FOREIGN KEY ventas_ibfk_3;
ALTER TABLE ventas DROP FOREIGN KEY ventas_ibfk_1;
ALTER TABLE ventas DROP FOREIGN KEY ventas_ibfk_2;
ALTER TABLE ventas DROP FOREIGN KEY ventas_ibfk_4;

-- Eliminar FK de facturas relacionada con oportunidades
ALTER TABLE facturas DROP FOREIGN KEY facturas_ibfk_1;
ALTER TABLE facturas DROP FOREIGN KEY facturas_ibfk_2;
ALTER TABLE facturas DROP FOREIGN KEY facturas_ibfk_3;

-- Eliminar FK de oportunidades_productos
ALTER TABLE oportunidades_productos DROP FOREIGN KEY oportunidades_productos_ibfk_1;
ALTER TABLE oportunidades_productos DROP FOREIGN KEY oportunidades_productos_ibfk_2;

-- Eliminar FK de fotos_productos
ALTER TABLE fotos_productos DROP FOREIGN KEY fotos_productos_ibfk_1;

-- Eliminar FK de detalles_compra
ALTER TABLE detalles_compra DROP FOREIGN KEY detalles_compra_ibfk_1;
ALTER TABLE detalles_compra DROP FOREIGN KEY detalles_compra_ibfk_2;

-- Eliminar FK de facturas_compras
ALTER TABLE facturas_compras DROP FOREIGN KEY facturas_compras_ibfk_1;

-- Eliminar FK de metodo_pago
ALTER TABLE metodo_pago DROP FOREIGN KEY metodo_pago_ibfk_1;

-- Eliminar FK de compras
ALTER TABLE compras DROP FOREIGN KEY compras_ibfk_2;

-- Eliminar FK de productos relacionada con proveedores
ALTER TABLE productos DROP FOREIGN KEY productos_ibfk_1;

-- Eliminar FK de sincronizaciones
ALTER TABLE sincronizaciones DROP FOREIGN KEY sincronizaciones_ibfk_1;

-- ============================================================
-- PASO 2: Eliminar tablas de compras
-- ============================================================
-- IMPORTANTE: Ejecuta primero el script 10-migrar-compras-a-ventas.sql
-- para migrar los datos antes de eliminar estas tablas

DROP TABLE IF EXISTS detalles_compra;
DROP TABLE IF EXISTS facturas_compras;
DROP TABLE IF EXISTS metodo_pago;
DROP TABLE IF EXISTS compras;

-- ============================================================
-- PASO 3: Eliminar tablas no usadas
-- ============================================================

DROP TABLE IF EXISTS oportunidades_productos;
DROP TABLE IF EXISTS oportunidades;
DROP TABLE IF EXISTS facturas;  -- Antigua tabla de facturas de ventas
DROP TABLE IF EXISTS ventas;    -- Antigua tabla de ventas mal diseñada
DROP TABLE IF EXISTS fotos_productos;

-- ============================================================
-- PASO 4: Eliminar tablas relacionadas con proveedores
-- ============================================================

DROP TABLE IF EXISTS sincronizaciones;
DROP TABLE IF EXISTS proveedores;

-- ============================================================
-- PASO 5: Eliminar campos innecesarios de productos
-- ============================================================
-- NOTA: MySQL/MariaDB no soporta IF EXISTS con DROP COLUMN en versiones antiguas
-- Si alguna columna no existe, simplemente ignora el error y continúa

ALTER TABLE productos DROP COLUMN proveedor_id;
ALTER TABLE productos DROP COLUMN imagen_path;
ALTER TABLE productos DROP COLUMN imagen;
ALTER TABLE productos DROP COLUMN foto;

-- ============================================================
-- PASO 6: Crear nueva estructura de ventas limpia
-- ============================================================

-- Tabla principal de ventas
CREATE TABLE IF NOT EXISTS ventas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    cliente_id INT(11) NOT NULL,
    usuario_id INT(11) NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    KEY cliente_id (cliente_id),
    KEY usuario_id (usuario_id),
    CONSTRAINT ventas_ibfk_1 FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
    CONSTRAINT ventas_ibfk_2 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de detalles de venta
CREATE TABLE IF NOT EXISTS detalles_venta (
    id INT(11) NOT NULL AUTO_INCREMENT,
    venta_id INT(11) NOT NULL,
    producto_id INT(11) NOT NULL,
    cantidad INT(3) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    descuento TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 si se aplicó descuento del 10%, 0 si no',
    PRIMARY KEY (id),
    KEY venta_id (venta_id),
    KEY producto_id (producto_id),
    CONSTRAINT detalles_venta_ibfk_1 FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    CONSTRAINT detalles_venta_ibfk_2 FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de facturas de ventas
CREATE TABLE IF NOT EXISTS facturas_ventas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    venta_id INT(11) NOT NULL,
    numero_factura VARCHAR(30) NOT NULL,
    numero_control VARCHAR(30) NOT NULL,
    fecha DATE NOT NULL DEFAULT (CURRENT_DATE),
    PRIMARY KEY (id),
    KEY venta_id (venta_id),
    CONSTRAINT facturas_ventas_ibfk_1 FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de métodos de pago para ventas
CREATE TABLE IF NOT EXISTS metodo_pago_ventas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    venta_id INT(11) NOT NULL,
    metodo VARCHAR(50) NOT NULL,
    numero_referencia VARCHAR(50) DEFAULT NULL,
    PRIMARY KEY (id),
    KEY venta_id (venta_id),
    CONSTRAINT metodo_pago_ventas_ibfk_1 FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- PASO 7: Crear trigger para descontar stock al registrar venta
-- ============================================================

DELIMITER $$

DROP TRIGGER IF EXISTS after_detalle_venta_insert$$

CREATE TRIGGER after_detalle_venta_insert
AFTER INSERT ON detalles_venta
FOR EACH ROW
BEGIN
    -- Descontar stock del producto
    UPDATE productos 
    SET stock = stock - NEW.cantidad 
    WHERE id = NEW.producto_id;
END$$

DELIMITER ;

-- ============================================================
-- FIN DEL SCRIPT
-- ============================================================
-- IMPORTANTE: Después de ejecutar este script, deberás:
-- 1. Actualizar el código PHP para usar las nuevas tablas de ventas
-- 2. Eliminar referencias a compras, proveedores, oportunidades, etc.
-- 3. Crear las páginas de gestión de ventas si no existen
-- ============================================================

