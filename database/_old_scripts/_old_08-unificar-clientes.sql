-- ============================================================
-- SCRIPT PARA UNIFICAR TABLAS DE CLIENTES
-- Compatible con phpMyAdmin
-- ============================================================
-- Este script:
-- 1. Agrega columnas telefono y email a la tabla clientes
-- 2. Migra los datos de clientes_telefonos y clientes_emails
-- 3. Elimina las tablas clientes_telefonos y clientes_emails
-- ============================================================

USE sistema_compras_zapatos;

-- ============================================================
-- PASO 1: Agregar columnas telefono y email a la tabla clientes
-- ============================================================

ALTER TABLE clientes 
ADD COLUMN telefono VARCHAR(20) DEFAULT NULL AFTER direccion,
ADD COLUMN email VARCHAR(255) DEFAULT NULL AFTER telefono;

-- ============================================================
-- PASO 2: Migrar datos de clientes_telefonos
-- ============================================================
-- Tomar el primer teléfono de cada cliente (si tiene múltiples, se toma el primero)
UPDATE clientes c
INNER JOIN (
    SELECT cliente_id, MIN(telefono) as telefono
    FROM clientes_telefonos
    GROUP BY cliente_id
) ct ON c.id = ct.cliente_id
SET c.telefono = ct.telefono;

-- ============================================================
-- PASO 3: Migrar datos de clientes_emails
-- ============================================================
-- Tomar el primer email de cada cliente (si tiene múltiples, se toma el primero)
UPDATE clientes c
INNER JOIN (
    SELECT cliente_id, MIN(email) as email
    FROM clientes_emails
    GROUP BY cliente_id
) ce ON c.id = ce.cliente_id
SET c.email = ce.email;

-- ============================================================
-- PASO 4: Eliminar Foreign Keys de las tablas a eliminar
-- ============================================================

-- Eliminar FK de clientes_emails
ALTER TABLE clientes_emails DROP FOREIGN KEY clientes_emails_ibfk_1;

-- Eliminar FK de clientes_telefonos
ALTER TABLE clientes_telefonos DROP FOREIGN KEY clientes_telefonos_ibfk_1;

-- ============================================================
-- PASO 5: Eliminar las tablas clientes_emails y clientes_telefonos
-- ============================================================

DROP TABLE IF EXISTS clientes_emails;
DROP TABLE IF EXISTS clientes_telefonos;

-- ============================================================
-- FIN DEL SCRIPT
-- ============================================================
-- NOTA: Si un cliente tenía múltiples teléfonos o emails,
-- solo se migró el primero. Si necesitas conservar todos,
-- considera usar campos separados por comas o JSON.
-- ============================================================

