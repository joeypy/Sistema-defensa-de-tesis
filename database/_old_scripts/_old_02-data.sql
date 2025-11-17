-- Script de datos iniciales
-- Este archivo se ejecuta después de 01-schema.sql

USE sistema_admin;

-- Insertar usuario administrador por defecto
-- Contraseña: admin123 (hash bcrypt)
-- IMPORTANTE: Cambiar esta contraseña en producción
INSERT IGNORE INTO usuarios (username, password, nombre, rol) VALUES
('admin', '$2y$12$sFVFhJTMxr2TD4li1VKnfOJ0s2qO/4kHNgmD9ydiU0TFR5aE2sHAi', 'Administrador', 'admin');

-- Nota: Para generar un nuevo hash de contraseña, usar en PHP:
-- password_hash('tu_contraseña', PASSWORD_BCRYPT)
