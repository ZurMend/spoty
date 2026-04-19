-- ============================================================
--  SOUNDWAVE — Base de datos v2 (roles admin/user)
--  Importar en phpMyAdmin: pestaña Importar → Continuar
-- ============================================================

CREATE DATABASE IF NOT EXISTS soundwave
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE soundwave;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS play_history;
DROP TABLE IF EXISTS playlist_songs;
DROP TABLE IF EXISTS playlists;
DROP TABLE IF EXISTS songs;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id           INT UNSIGNED         AUTO_INCREMENT PRIMARY KEY,
  nombre       VARCHAR(120)         NOT NULL,
  email        VARCHAR(191)         NOT NULL UNIQUE,
  password     VARCHAR(255)         NOT NULL,
  avatar       VARCHAR(300)         DEFAULT NULL,
  role         ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at   TIMESTAMP            DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE songs (
  id                INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  nombre            VARCHAR(200)  NOT NULL,
  artista           VARCHAR(200)  NOT NULL,
  genero            VARCHAR(100)  NOT NULL,
  duracion          INT UNSIGNED  NOT NULL DEFAULT 0,
  imagen            VARCHAR(300)  DEFAULT NULL,
  album             VARCHAR(200)  DEFAULT NULL,
  fecha_lanzamiento DATE          DEFAULT NULL,
  archivo           VARCHAR(300)  NOT NULL,
  subido_por        INT UNSIGNED  DEFAULT NULL,
  created_at        TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (subido_por) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE playlists (
  id          INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED  NOT NULL,
  nombre      VARCHAR(200)  NOT NULL,
  descripcion TEXT          DEFAULT NULL,
  portada     VARCHAR(300)  DEFAULT NULL,
  created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE playlist_songs (
  id          INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  playlist_id INT UNSIGNED  NOT NULL,
  song_id     INT UNSIGNED  NOT NULL,
  orden       INT UNSIGNED  DEFAULT 0,
  added_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
  FOREIGN KEY (song_id)     REFERENCES songs(id)     ON DELETE CASCADE,
  UNIQUE KEY unique_song_in_playlist (playlist_id, song_id)
) ENGINE=InnoDB;

CREATE TABLE play_history (
  id         INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED  NOT NULL,
  song_id    INT UNSIGNED  NOT NULL,
  played_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
--  ADMINISTRADOR POR DEFECTO
--  Email:      admin@soundwave.com
--  Contraseña: admin123
-- ============================================================
INSERT INTO users (nombre, email, password, role) VALUES
  ('Administrador', 'admin@soundwave.com',
   'y0.G9OZlQlwr3yJaXF6e',
   'admin');
