-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-04-2026 a las 13:59:55
-- Versión del servidor: 10.4.6-MariaDB
-- Versión de PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `soundplay_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `albumes`
--

CREATE TABLE `albumes` (
  `id_album` int(11) NOT NULL,
  `id_artista` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `portada_ruta` varchar(255) DEFAULT NULL,
  `fecha_publicacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `albumes`
--

INSERT INTO `albumes` (`id_album`, `id_artista`, `titulo`, `portada_ruta`, `fecha_publicacion`) VALUES
(1, 2, 'Void Protocol', 'assets/img/albumes/void_protocol.jpg', '2023-05-15'),
(2, 2, 'System Error', 'assets/img/albumes/system_error.jpg', '2021-11-20'),
(3, 3, 'Neon Pulse', 'assets/img/albumes/neon_pulse.jpg', '2024-01-10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `artistas`
--

CREATE TABLE `artistas` (
  `id_artista` int(11) NOT NULL,
  `nombre_artistico` varchar(255) NOT NULL,
  `bio_extended` text DEFAULT NULL,
  `localidad` varchar(100) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `espacio_maximo` bigint(20) DEFAULT 524288000
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `artistas`
--

INSERT INTO `artistas` (`id_artista`, `nombre_artistico`, `bio_extended`, `localidad`, `foto_perfil`, `espacio_maximo`) VALUES
(2, 'Valentina Void', 'Originaria de las calles neón de Berlín, fusiona techno industrial con texturas cinemáticas.', 'Berlín', 'assets/img/artistas/valentina.jpg', 524288000),
(3, 'Nova Eclipse', 'Explorador de sintetizadores modales y paisajes sonoros ambientales.', 'CDMX', 'assets/img/artistas/nova.jpg', 524288000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `canciones`
--

CREATE TABLE `canciones` (
  `id_cancion` int(11) NOT NULL,
  `id_album` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `archivo_ruta` varchar(255) NOT NULL,
  `duracion` time DEFAULT NULL,
  `genero` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `canciones`
--

INSERT INTO `canciones` (`id_cancion`, `id_album`, `titulo`, `archivo_ruta`, `duracion`, `genero`) VALUES
(1, 1, 'Digital Silence', 'uploads/musica/digital_silence.mp3', '00:03:42', 'Techno'),
(2, 1, 'Subterranean', 'uploads/musica/subterranean.mp3', '00:05:08', 'Techno'),
(3, 3, 'Electric Dreams', 'uploads/musica/electric_dreams.mp3', '00:04:15', 'Synthwave');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `creditos_tecnicos`
--

CREATE TABLE `creditos_tecnicos` (
  `id_credito` int(11) NOT NULL,
  `id_cancion` int(11) NOT NULL,
  `nombre_profesional` varchar(255) NOT NULL,
  `rol_tecnico` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `creditos_tecnicos`
--

INSERT INTO `creditos_tecnicos` (`id_credito`, `id_cancion`, `nombre_profesional`, `rol_tecnico`) VALUES
(1, 1, 'Julian Casablancas', 'Productor Principal'),
(2, 1, 'Valentina Void', 'Ingeniera de Mezcla'),
(3, 3, 'Nova Eclipse', 'Compositor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transacciones`
--

CREATE TABLE `transacciones` (
  `id_pago` int(11) NOT NULL,
  `id_usuario_emisor` int(11) NOT NULL,
  `id_artista_receptor` int(11) NOT NULL,
  `importe_total` decimal(10,2) NOT NULL,
  `iva_aplicado` decimal(10,2) NOT NULL,
  `fecha_pago` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `transacciones`
--

INSERT INTO `transacciones` (`id_pago`, `id_usuario_emisor`, `id_artista_receptor`, `importe_total`, `iva_aplicado`, `fecha_pago`) VALUES
(1, 4, 2, '10.00', '2.10', '2026-04-26 13:50:34'),
(2, 4, 3, '5.00', '1.05', '2026-04-26 13:50:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','artista','user') DEFAULT 'user',
  `consentimiento_rgpd` tinyint(1) DEFAULT 0,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `email`, `password`, `rol`, `consentimiento_rgpd`, `fecha_registro`) VALUES
(1, 'admin@soundplay.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, '2026-04-26 11:50:33'),
(2, 'valentina@void.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'artista', 1, '2026-04-26 11:50:33'),
(3, 'nova@eclipse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'artista', 1, '2026-04-26 11:50:33'),
(4, 'fan@music.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, '2026-04-26 11:50:33');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `albumes`
--
ALTER TABLE `albumes`
  ADD PRIMARY KEY (`id_album`),
  ADD KEY `id_artista` (`id_artista`);

--
-- Indices de la tabla `artistas`
--
ALTER TABLE `artistas`
  ADD PRIMARY KEY (`id_artista`);

--
-- Indices de la tabla `canciones`
--
ALTER TABLE `canciones`
  ADD PRIMARY KEY (`id_cancion`),
  ADD KEY `id_album` (`id_album`);

--
-- Indices de la tabla `creditos_tecnicos`
--
ALTER TABLE `creditos_tecnicos`
  ADD PRIMARY KEY (`id_credito`),
  ADD KEY `id_cancion` (`id_cancion`);

--
-- Indices de la tabla `transacciones`
--
ALTER TABLE `transacciones`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_usuario_emisor` (`id_usuario_emisor`),
  ADD KEY `id_artista_receptor` (`id_artista_receptor`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `albumes`
--
ALTER TABLE `albumes`
  MODIFY `id_album` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `canciones`
--
ALTER TABLE `canciones`
  MODIFY `id_cancion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `creditos_tecnicos`
--
ALTER TABLE `creditos_tecnicos`
  MODIFY `id_credito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `transacciones`
--
ALTER TABLE `transacciones`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `albumes`
--
ALTER TABLE `albumes`
  ADD CONSTRAINT `albumes_ibfk_1` FOREIGN KEY (`id_artista`) REFERENCES `artistas` (`id_artista`) ON DELETE CASCADE;

--
-- Filtros para la tabla `artistas`
--
ALTER TABLE `artistas`
  ADD CONSTRAINT `artistas_ibfk_1` FOREIGN KEY (`id_artista`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `canciones`
--
ALTER TABLE `canciones`
  ADD CONSTRAINT `canciones_ibfk_1` FOREIGN KEY (`id_album`) REFERENCES `albumes` (`id_album`) ON DELETE CASCADE;

--
-- Filtros para la tabla `creditos_tecnicos`
--
ALTER TABLE `creditos_tecnicos`
  ADD CONSTRAINT `creditos_tecnicos_ibfk_1` FOREIGN KEY (`id_cancion`) REFERENCES `canciones` (`id_cancion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `transacciones`
--
ALTER TABLE `transacciones`
  ADD CONSTRAINT `transacciones_ibfk_1` FOREIGN KEY (`id_usuario_emisor`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `transacciones_ibfk_2` FOREIGN KEY (`id_artista_receptor`) REFERENCES `artistas` (`id_artista`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
