-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-04-2026 a las 01:54:54
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `soundwave`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `playlists`
--

CREATE TABLE `playlists` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `portada` varchar(300) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `playlists`
--

INSERT INTO `playlists` (`id`, `user_id`, `nombre`, `descripcion`, `portada`, `created_at`) VALUES
(1, 3, 'Cool', 'Coolllll', 'uploads/covers/pl_69e2e37cb24696.82927356.jpg', '2026-04-18 01:50:52'),
(2, 2, 'Cool', '6tyuhoipkiogy', 'uploads/covers/pl_69e2f374cace05.03329521.jpg', '2026-04-18 02:59:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `playlist_songs`
--

CREATE TABLE `playlist_songs` (
  `id` int(10) UNSIGNED NOT NULL,
  `playlist_id` int(10) UNSIGNED NOT NULL,
  `song_id` int(10) UNSIGNED NOT NULL,
  `orden` int(10) UNSIGNED DEFAULT 0,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `playlist_songs`
--

INSERT INTO `playlist_songs` (`id`, `playlist_id`, `song_id`, `orden`, `added_at`) VALUES
(1, 1, 4, 1, '2026-04-18 01:51:16'),
(2, 1, 1, 2, '2026-04-18 01:55:08'),
(3, 2, 2, 1, '2026-04-18 02:59:12'),
(4, 2, 3, 2, '2026-04-18 02:59:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `play_history`
--

CREATE TABLE `play_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `song_id` int(10) UNSIGNED NOT NULL,
  `played_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `play_history`
--

INSERT INTO `play_history` (`id`, `user_id`, `song_id`, `played_at`) VALUES
(1, 2, 2, '2026-04-18 01:40:28'),
(2, 2, 2, '2026-04-18 01:40:28'),
(3, 2, 2, '2026-04-18 01:40:51'),
(4, 2, 2, '2026-04-18 01:40:51'),
(5, 3, 4, '2026-04-18 02:01:57'),
(6, 3, 4, '2026-04-18 02:01:57'),
(7, 2, 2, '2026-04-18 02:47:13'),
(8, 2, 2, '2026-04-18 02:47:13'),
(9, 1, 1, '2026-04-18 16:00:32'),
(10, 1, 2, '2026-04-18 16:00:37'),
(11, 1, 6, '2026-04-18 16:18:49'),
(12, 1, 6, '2026-04-18 16:19:19'),
(13, 1, 7, '2026-04-18 16:45:50'),
(14, 1, 7, '2026-04-18 16:45:50'),
(15, 1, 7, '2026-04-18 19:46:12'),
(16, 1, 7, '2026-04-18 19:46:12'),
(17, 1, 7, '2026-04-18 19:46:14'),
(18, 1, 7, '2026-04-18 19:46:14'),
(19, 1, 7, '2026-04-18 19:46:14'),
(20, 1, 7, '2026-04-18 19:46:17'),
(21, 1, 8, '2026-04-18 20:01:09'),
(22, 1, 8, '2026-04-18 20:01:09'),
(23, 1, 7, '2026-04-18 20:01:27'),
(24, 1, 7, '2026-04-18 20:01:27'),
(25, 1, 2, '2026-04-18 20:01:36'),
(26, 1, 2, '2026-04-18 20:01:36'),
(27, 1, 10, '2026-04-18 20:10:06'),
(28, 1, 9, '2026-04-18 20:10:33'),
(29, 1, 8, '2026-04-18 20:14:23'),
(30, 1, 21, '2026-04-18 23:00:10'),
(31, 1, 21, '2026-04-18 23:00:21'),
(32, 1, 21, '2026-04-18 23:00:21'),
(33, 1, 20, '2026-04-18 23:00:42'),
(34, 1, 20, '2026-04-18 23:00:42'),
(35, 1, 2, '2026-04-18 23:00:52'),
(36, 1, 2, '2026-04-18 23:00:52'),
(37, 1, 3, '2026-04-18 23:00:54'),
(38, 1, 3, '2026-04-18 23:00:54'),
(39, 1, 8, '2026-04-18 23:00:56'),
(40, 1, 8, '2026-04-18 23:00:56'),
(41, 1, 12, '2026-04-18 23:01:19'),
(42, 1, 12, '2026-04-18 23:01:19'),
(43, 1, 21, '2026-04-18 23:14:23'),
(44, 1, 21, '2026-04-18 23:14:23'),
(45, 1, 17, '2026-04-18 23:14:29'),
(46, 1, 17, '2026-04-18 23:14:29'),
(47, 1, 9, '2026-04-18 23:14:32'),
(48, 1, 9, '2026-04-18 23:14:33'),
(49, 1, 14, '2026-04-18 23:15:14'),
(50, 1, 14, '2026-04-18 23:15:15'),
(51, 1, 21, '2026-04-18 23:26:15'),
(52, 1, 21, '2026-04-18 23:26:15'),
(53, 1, 17, '2026-04-18 23:51:31'),
(54, 1, 17, '2026-04-18 23:51:31'),
(55, 1, 16, '2026-04-18 23:51:36'),
(56, 1, 16, '2026-04-18 23:51:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `songs`
--

CREATE TABLE `songs` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `artista` varchar(200) NOT NULL,
  `genero` varchar(100) NOT NULL,
  `duracion` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `imagen` varchar(300) DEFAULT NULL,
  `album` varchar(200) DEFAULT NULL,
  `fecha_lanzamiento` date DEFAULT NULL,
  `archivo` varchar(300) NOT NULL,
  `subido_por` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `songs`
--

INSERT INTO `songs` (`id`, `nombre`, `artista`, `genero`, `duracion`, `imagen`, `album`, `fecha_lanzamiento`, `archivo`, `subido_por`, `created_at`) VALUES
(1, 'Blinding Lights', 'The Weeknd', 'pop', 200, NULL, 'After Hours', '2019-11-29', 'uploads/songs/blinding_lights.mp3', NULL, '2026-04-17 14:58:09'),
(2, 'Bohemian Rhapsody', 'Queen', 'rock', 354, NULL, 'A Night at the Opera', '1975-10-31', 'uploads/songs/bohemian_rhapsody.mp3', NULL, '2026-04-17 14:58:09'),
(3, 'HUMBLE.', 'Kendrick Lamar', 'hip-hop', 177, NULL, 'DAMN.', '2017-04-07', 'uploads/songs/humble.mp3', NULL, '2026-04-17 14:58:09'),
(4, 'Starboy', 'The Weeknd', 'pop', 230, NULL, 'Starboy', '2016-11-25', 'uploads/songs/starboy.mp3', NULL, '2026-04-17 14:58:09'),
(6, 'Call Out My Name', 'The weeknd', 'r&b', 238, 'uploads/covers/cover_69e3aedf937110.80146702.jpg', 'My Dear Melancholy', '2018-10-18', 'uploads/songs/song_69e3aedf92b3d2.78894492.mp3', 1, '2026-04-18 16:18:39'),
(7, 'Ahora te puedes marchar', 'Luis Miguel', 'pop', 190, 'uploads/covers/cover_69e3b537298859.53690951.jpg', 'Soy como Quiero Ser', '1987-08-20', 'uploads/songs/song_69e3b53728fe05.02991140.mp3', 1, '2026-04-18 16:45:43'),
(8, 'Luther', 'Kendrick', 'hip-hop', 178, 'uploads/covers/cover_69e3e2662666a3.36711081.jpg', 'GNX', '2024-06-04', 'uploads/songs/song_69e3e26625d586.67653109.mp3', 1, '2026-04-18 19:58:30'),
(9, 'La Mentira', 'Luis Miguel', 'latin', 227, 'uploads/covers/cover_69e3e3f6c7cec7.66102398.jpg', 'Romance', '1991-07-23', 'uploads/songs/song_69e3e3f6c74e72.70135677.mp3', 1, '2026-04-18 20:05:10'),
(10, 'Animals', 'Martin Garrix', 'electronica', 192, 'uploads/covers/cover_69e3e50a9d1de8.97276558.jpg', 'Animals', '2014-06-18', 'uploads/songs/song_69e3e50a9c9ee3.78481738.mp3', 1, '2026-04-18 20:09:46'),
(11, 'Cuando Seas Grande', 'Miguel Mateos & Zas', 'rock', 267, 'uploads/covers/cover_69e3eb6567af59.65862314.jpg', 'Solos en America', '1987-06-20', 'uploads/songs/song_69e3eb65673348.59454181.mp3', 1, '2026-04-18 20:36:53'),
(12, 'Roadhouse', 'Morrison', 'jazz', 244, 'uploads/covers/cover_69e3ed0b947900.70356694.jpg', 'Horry', '2006-03-01', 'uploads/songs/song_69e3ed0b93ee09.97385420.mp3', 1, '2026-04-18 20:43:55'),
(13, 'Bad', 'David Guetta & Showtek', 'electronica', 171, 'uploads/covers/cover_69e3ff082c3331.17375595.jpg', 'Listen', '2014-03-12', 'uploads/songs/song_69e3ff082bb0c8.01302945.mp3', 1, '2026-04-18 22:00:40'),
(14, 'De Musica Ligera', 'Soda Stero', 'rock', 210, 'uploads/covers/cover_69e400af69d017.11295468.jpg', 'ACA', NULL, 'uploads/songs/song_69e400af693d02.78731868.mp3', 1, '2026-04-18 22:07:43'),
(15, 'Uptown Funk', 'Mark Ronson ft. Bruno Mars', 'pop', 270, 'uploads/covers/cover_69e40275ee76d0.39258416.jpg', 'Uptown Special', '2014-01-07', 'uploads/songs/song_69e40275edeee2.79366542.mp3', 1, '2026-04-18 22:15:17'),
(16, 'Congratulations', 'Post Malone', 'hip-hop', 220, 'uploads/covers/cover_69e403894933a0.76103790.jpg', 'Stoney', '2014-02-12', 'uploads/songs/song_69e403894894a3.79175562.mp3', 1, '2026-04-18 22:19:53'),
(17, 'Tuesday', 'Danelle Sandoval', 'electronica', 193, 'uploads/covers/cover_69e404a00d08c5.39176352.jpg', 'Sencillo', '2012-11-14', 'uploads/songs/song_69e404a00c76b7.45539649.mp3', 1, '2026-04-18 22:24:32'),
(18, 'Fly Me To The Moon', 'Frank Sinatra', 'jazz', 146, 'uploads/covers/cover_69e405c7301779.05940180.jpg', 'Nothing But The Best', '2001-06-05', 'uploads/songs/song_69e405c72f9b65.75754297.mp3', 1, '2026-04-18 22:29:27'),
(19, 'My Way', 'Frank Sinatra', 'jazz', 247, 'uploads/covers/cover_69e40695b67264.72265386.jpg', 'Nothing But The Best', '2004-06-09', 'uploads/songs/song_69e40695b5df40.24720679.mp3', 1, '2026-04-18 22:32:53'),
(20, 'Hoy te Vi', 'Sebas Barcenas', 'latin', 235, 'uploads/covers/cover_69e408f382f851.73618833.jpg', 'Sencillo', '2026-01-06', 'uploads/songs/song_69e408f3823bf4.33209435.mp3', 1, '2026-04-18 22:42:59'),
(21, 'La Media Vuelta', 'Luis Miguel', 'latin', 203, 'uploads/covers/cover_69e40a9cab1885.68978710.jpg', 'Romance', '2009-07-08', 'uploads/songs/song_69e40a9caa6ce8.85990122.mp3', 1, '2026-04-18 22:50:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(300) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `nombre`, `email`, `password`, `avatar`, `created_at`, `role`) VALUES
(1, 'Admin', 'admin@gmail.com', '$2y$10$eyHxtCNYVDc3DbXI0kydneJi5dPa86st1Kk.G9OZlQlwr3yJaXF6e', NULL, '2026-04-17 14:58:09', 'admin'),
(2, 'Zuri', 'zuri@gmail.com', '$2y$12$/Q.kxKHz6nQPL9fyiSFYz.qyGOJKMyKdmSfgjssDyfMChMKBLQFVW', NULL, '2026-04-17 15:02:31', 'user'),
(3, 'Isaac', 'isaac@gmail.com', '$2y$12$AyyWBo7a9DtF0nrUNyu3luQRNMss9gSBHD/gjt3t2m1MEac4TonPu', NULL, '2026-04-18 01:48:44', 'user');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `playlists`
--
ALTER TABLE `playlists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `playlist_songs`
--
ALTER TABLE `playlist_songs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_song_in_playlist` (`playlist_id`,`song_id`),
  ADD KEY `song_id` (`song_id`);

--
-- Indices de la tabla `play_history`
--
ALTER TABLE `play_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `song_id` (`song_id`);

--
-- Indices de la tabla `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subido_por` (`subido_por`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `playlists`
--
ALTER TABLE `playlists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `playlist_songs`
--
ALTER TABLE `playlist_songs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `play_history`
--
ALTER TABLE `play_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `songs`
--
ALTER TABLE `songs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `playlists`
--
ALTER TABLE `playlists`
  ADD CONSTRAINT `playlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `playlist_songs`
--
ALTER TABLE `playlist_songs`
  ADD CONSTRAINT `playlist_songs_ibfk_1` FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `playlist_songs_ibfk_2` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `play_history`
--
ALTER TABLE `play_history`
  ADD CONSTRAINT `play_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `play_history_ibfk_2` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `songs`
--
ALTER TABLE `songs`
  ADD CONSTRAINT `songs_ibfk_1` FOREIGN KEY (`subido_por`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
