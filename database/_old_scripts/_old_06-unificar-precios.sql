-- ============================================================
-- SCRIPT PARA UNIFICAR PRECIOS: precio_compra y precio_venta -> precio
-- ============================================================
-- Este script elimina precio_compra y precio_venta,
-- y crea una única columna 'precio'
-- ============================================================

USE sistema_compras_zapatos;

-- 1. Agregar columna precio (usaremos precio_compra como base)
ALTER TABLE `productos` 
ADD COLUMN `precio` decimal(5,2) NOT NULL DEFAULT 0.00 AFTER `color`;

-- 2. Copiar valores de precio_compra a precio
UPDATE `productos` 
SET `precio` = `precio_compra`;

-- 3. Eliminar columnas antiguas
ALTER TABLE `productos` 
DROP COLUMN `precio_compra`,
DROP COLUMN `precio_venta`;

-- 4. Actualizar tabla historial_precios (eliminar y recrear con solo precio)
DROP TABLE IF EXISTS `historial_precios`;

CREATE TABLE `historial_precios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `precio` decimal(5,2) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `historial_precios_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Recrear trigger para historial de precios
DROP TRIGGER IF EXISTS `after_precio_update`;

DELIMITER $$
CREATE TRIGGER `after_precio_update` AFTER UPDATE ON `productos` 
FOR EACH ROW 
BEGIN
    IF OLD.precio <> NEW.precio THEN
        INSERT INTO historial_precios (producto_id, precio)
        VALUES (NEW.id, NEW.precio);
    END IF;
END$$
DELIMITER ;

-- ============================================================
-- VERIFICACIÓN
-- ============================================================
-- Ejecutar para verificar:
-- DESCRIBE productos;
-- DESCRIBE historial_precios;
-- ============================================================

