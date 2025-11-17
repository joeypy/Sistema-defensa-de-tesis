-- ============================================================
-- SCRIPT PARA MIGRAR DATOS DE COMPRAS A VENTAS
-- Compatible con phpMyAdmin
-- ============================================================
-- Este script migra los datos de las tablas de compras
-- a las nuevas tablas de ventas, manteniendo compatibilidad
-- ============================================================
-- IMPORTANTE: Ejecuta este script ANTES de eliminar las tablas de compras
-- ============================================================

USE sistema_compras_zapatos;

-- ============================================================
-- PASO 0: Verificar y crear tablas de ventas si no existen
-- ============================================================

-- Crear tabla ventas si no existe
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

-- Crear tabla detalles_venta si no existe
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

-- Crear tabla facturas_ventas si no existe
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

-- Crear tabla metodo_pago_ventas si no existe
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
-- PASO 1: Verificar y migrar datos de compras a ventas
-- ============================================================
-- Solo migra si la tabla compras existe y tiene datos

-- Verificar si existe la tabla compras antes de migrar
SET @table_compras_exists = (
    SELECT COUNT(*) 
    FROM information_schema.tables 
    WHERE table_schema = 'sistema_compras_zapatos' 
    AND table_name = 'compras'
);

-- Migrar compras → ventas (solo si la tabla existe)
SET @sql_migrar_ventas = '
INSERT INTO ventas (id, cliente_id, usuario_id, fecha, total)
SELECT 
    c.id,
    COALESCE(c.cliente_id, 1) as cliente_id,
    c.usuario_id,
    c.fecha,
    c.total
FROM compras c
WHERE NOT EXISTS (
    SELECT 1 FROM ventas v WHERE v.id = c.id
)
AND c.cliente_id IS NOT NULL
';

-- Ejecutar solo si la tabla existe
SET @sql_exec = IF(@table_compras_exists > 0, @sql_migrar_ventas, 'SELECT "Tabla compras no existe, omitiendo migración" as mensaje');
PREPARE stmt FROM @sql_exec;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Si hay compras sin cliente, puedes decidir:
-- Opción 1: Crear un cliente "Sin especificar" y migrarlas
-- Opción 2: No migrarlas (ya está excluido con el WHERE)

-- ============================================================
-- PASO 2: Migrar detalles_compra a detalles_venta
-- ============================================================
-- Solo migra si la tabla detalles_compra existe

SET @table_detalles_compra_exists = (
    SELECT COUNT(*) 
    FROM information_schema.tables 
    WHERE table_schema = 'sistema_compras_zapatos' 
    AND table_name = 'detalles_compra'
);

SET @sql_migrar_detalles = '
INSERT INTO detalles_venta (id, venta_id, producto_id, cantidad, precio_unitario, subtotal, descuento)
SELECT 
    dc.id,
    dc.compra_id as venta_id,
    dc.producto_id,
    dc.cantidad,
    dc.precio_unitario,
    dc.subtotal,
    COALESCE(dc.descuento, 0) as descuento
FROM detalles_compra dc
INNER JOIN ventas v ON v.id = dc.compra_id
WHERE NOT EXISTS (
    SELECT 1 FROM detalles_venta dv WHERE dv.id = dc.id
)
';

SET @sql_exec = IF(@table_detalles_compra_exists > 0, @sql_migrar_detalles, 'SELECT "Tabla detalles_compra no existe, omitiendo migración" as mensaje');
PREPARE stmt FROM @sql_exec;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- PASO 3: Migrar facturas_compras a facturas_ventas
-- ============================================================
-- Solo migra si la tabla facturas_compras existe

SET @table_facturas_compras_exists = (
    SELECT COUNT(*) 
    FROM information_schema.tables 
    WHERE table_schema = 'sistema_compras_zapatos' 
    AND table_name = 'facturas_compras'
);

SET @sql_migrar_facturas = '
INSERT INTO facturas_ventas (id, venta_id, numero_factura, numero_control, fecha)
SELECT 
    fc.id,
    fc.compra_id as venta_id,
    fc.numero_factura,
    fc.numero_control,
    fc.fecha
FROM facturas_compras fc
INNER JOIN ventas v ON v.id = fc.compra_id
WHERE NOT EXISTS (
    SELECT 1 FROM facturas_ventas fv WHERE fv.id = fc.id
)
';

SET @sql_exec = IF(@table_facturas_compras_exists > 0, @sql_migrar_facturas, 'SELECT "Tabla facturas_compras no existe, omitiendo migración" as mensaje');
PREPARE stmt FROM @sql_exec;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- PASO 4: Migrar metodo_pago a metodo_pago_ventas
-- ============================================================
-- Solo migra si la tabla metodo_pago existe

SET @table_metodo_pago_exists = (
    SELECT COUNT(*) 
    FROM information_schema.tables 
    WHERE table_schema = 'sistema_compras_zapatos' 
    AND table_name = 'metodo_pago'
);

SET @sql_migrar_metodo = '
INSERT INTO metodo_pago_ventas (id, venta_id, metodo, numero_referencia)
SELECT 
    mp.id,
    mp.compra_id as venta_id,
    mp.metodo,
    mp.numero_referencia
FROM metodo_pago mp
INNER JOIN ventas v ON v.id = mp.compra_id
WHERE NOT EXISTS (
    SELECT 1 FROM metodo_pago_ventas mpv WHERE mpv.id = mp.id
)
';

SET @sql_exec = IF(@table_metodo_pago_exists > 0, @sql_migrar_metodo, 'SELECT "Tabla metodo_pago no existe, omitiendo migración" as mensaje');
PREPARE stmt FROM @sql_exec;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- PASO 5: Ajustar AUTO_INCREMENT de las tablas nuevas
-- ============================================================
-- Esto asegura que los nuevos IDs no entren en conflicto
-- Solo ajusta si las tablas tienen datos

-- Verificar y ajustar AUTO_INCREMENT de ventas
SET @max_venta_id = (SELECT COALESCE(MAX(id), 0) FROM ventas);
SET @sql_ventas = CONCAT('ALTER TABLE ventas AUTO_INCREMENT = ', GREATEST(@max_venta_id + 1, 1));
PREPARE stmt FROM @sql_ventas;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar y ajustar AUTO_INCREMENT de detalles_venta
SET @max_detalle_id = (SELECT COALESCE(MAX(id), 0) FROM detalles_venta);
SET @sql_detalles = CONCAT('ALTER TABLE detalles_venta AUTO_INCREMENT = ', GREATEST(@max_detalle_id + 1, 1));
PREPARE stmt FROM @sql_detalles;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar y ajustar AUTO_INCREMENT de facturas_ventas
SET @max_factura_id = (SELECT COALESCE(MAX(id), 0) FROM facturas_ventas);
SET @sql_facturas = CONCAT('ALTER TABLE facturas_ventas AUTO_INCREMENT = ', GREATEST(@max_factura_id + 1, 1));
PREPARE stmt FROM @sql_facturas;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar y ajustar AUTO_INCREMENT de metodo_pago_ventas
SET @max_metodo_id = (SELECT COALESCE(MAX(id), 0) FROM metodo_pago_ventas);
SET @sql_metodo = CONCAT('ALTER TABLE metodo_pago_ventas AUTO_INCREMENT = ', GREATEST(@max_metodo_id + 1, 1));
PREPARE stmt FROM @sql_metodo;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- PASO 6: IMPORTANTE - Ajustar stock de productos
-- ============================================================
-- Como las "compras" eran en realidad "ventas", el stock ya fue descontado
-- Pero si las compras aumentaban el stock, necesitamos revertir eso
-- 
-- Si las compras AUMENTABAN el stock (stock = stock + cantidad):
-- Necesitamos DESCONTAR ese stock que se agregó incorrectamente
--
-- Si las compras YA DESCONTABAN el stock (stock = stock - cantidad):
-- No necesitamos hacer nada, el stock ya está correcto
--
-- NOTA: Ajusta esta lógica según cómo funcionaba tu sistema anterior

-- Opción 1: Si las compras AUMENTABAN el stock incorrectamente, revierte:
/*
UPDATE productos p
INNER JOIN (
    SELECT producto_id, SUM(cantidad) as total_cantidad
    FROM detalles_compra
    GROUP BY producto_id
) dc ON p.id = dc.producto_id
SET p.stock = p.stock - dc.total_cantidad;
*/

-- Opción 2: Si las compras YA DESCONTABAN el stock, no hagas nada
-- (comenta la query anterior)

-- ============================================================
-- FIN DEL SCRIPT
-- ============================================================
-- Después de ejecutar este script:
-- 1. Verifica que los datos se migraron correctamente
-- 2. Verifica que el stock de productos es correcto
-- 3. Luego ejecuta el script 09-simplificar-solo-ventas.sql para limpiar
-- ============================================================

