-- Script para modificar la tabla metodo_pago
-- Permite que numero_referencia sea NULL para pagos en efectivo
-- Elimina la columna monto que no cumple función lógica

-- Modificar la columna numero_referencia para permitir NULL
ALTER TABLE `metodo_pago` 
MODIFY COLUMN `numero_referencia` varchar(4) NULL;

-- Actualizar registros existentes de efectivo que tengan valores vacíos o 'efec' a NULL
UPDATE `metodo_pago` 
SET `numero_referencia` = NULL 
WHERE `metodo` = 'Efectivo' OR `metodo` = 'efectivo' 
   OR `numero_referencia` = '' 
   OR `numero_referencia` = 'efec';

-- Eliminar la columna monto que no cumple función lógica
ALTER TABLE `metodo_pago` 
DROP COLUMN `monto`;

