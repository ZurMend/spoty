-- ============================================================
--  reset_admin.sql
--  Ejecuta esto si ya tienes la BD y solo quieres
--  crear o actualizar el usuario administrador.
--
--  Abre phpMyAdmin → selecciona BD soundwave →
--  pestaña SQL → pega esto → clic en Continuar
-- ============================================================

USE soundwave;

-- Agregar columna role si no existe (por si tienes BD antigua)
ALTER TABLE users
  MODIFY COLUMN role ENUM('admin','user') NOT NULL DEFAULT 'user';

-- Borrar admin anterior si existe
DELETE FROM users WHERE email = 'admin@soundwave.com';

-- Insertar admin con contraseña: admin123
INSERT INTO users (nombre, email, password, role) VALUES
  ('Administrador', 'admin@soundwave.com',
   '$2y$10$eyHxtCNYVDc3DbXI0kydneJi5dPa86st1Kk.G9OZlQlwr3yJaXF6e',
   'admin');

-- Ver todos los usuarios con su rol
SELECT id, nombre, email, role, created_at FROM users;
