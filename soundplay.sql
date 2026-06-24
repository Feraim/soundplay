SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================================
-- Base de datos: `soundplay`
-- Orden: usuarios → artistas → albumes → canciones
--                                      → creditos_tecnicos
--        artistas + usuarios → transacciones
-- ============================================================

-- --------------------------------------------------------
-- Tabla: `usuarios`  (raíz, sin dependencias)
-- --------------------------------------------------------
CREATE TABLE `usuarios` (
  `id_usuario`              int(11)   NOT NULL AUTO_INCREMENT,
  `email`                   varchar(255) NOT NULL,
  `contrasena`              varchar(255) NOT NULL,
  `rol`                     enum('admin','artista','user') NOT NULL,
  `consentimiento_rgpd`     tinyint(1)   NOT NULL DEFAULT 0,
  `fecha_registro`          timestamp    NOT NULL DEFAULT current_timestamp(),
  `banned`                  tinyint(1)   NOT NULL DEFAULT 0,
  `recomendaciones_activas` tinyint(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uq_usuario_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5;

-- --------------------------------------------------------
-- Tabla: `artistas`  (depende de: usuarios)
-- --------------------------------------------------------
CREATE TABLE `artistas` (
  `id_artista`       int(11)      NOT NULL AUTO_INCREMENT,
  `nombre_artistico` varchar(100) NOT NULL,
  `bio_extended`     text         DEFAULT NULL,
  `localidad`        varchar(100) DEFAULT NULL,
  `foto_perfil`      varchar(255) DEFAULT NULL,
  `espacio_maximo`   bigint(20)   DEFAULT NULL COMMENT 'Espacio disponible en bytes',
  PRIMARY KEY (`id_artista`),
  CONSTRAINT `fk_artista_usuario`
    FOREIGN KEY (`id_artista`) REFERENCES `usuarios` (`id_usuario`)
    ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4;

-- --------------------------------------------------------
-- Tabla: `albumes`  (depende de: artistas)
-- --------------------------------------------------------
CREATE TABLE `albumes` (
  `id_album`          int(11)      NOT NULL AUTO_INCREMENT,
  `id_artista`        int(11)      NOT NULL,
  `titulo`            varchar(150) NOT NULL,
  `portada_ruta`      varchar(255) DEFAULT NULL,
  `fecha_publicacion` date         DEFAULT NULL,
  PRIMARY KEY (`id_album`),
  KEY `idx_album_artista` (`id_artista`),
  CONSTRAINT `fk_album_artista`
    FOREIGN KEY (`id_artista`) REFERENCES `artistas` (`id_artista`)
    ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2;

-- --------------------------------------------------------
-- Tabla: `canciones`  (depende de: albumes)
-- --------------------------------------------------------
CREATE TABLE `canciones` (
  `id_cancion`     int(11)      NOT NULL AUTO_INCREMENT,
  `id_album`       int(11)      NOT NULL,
  `titulo`         varchar(150) NOT NULL,
  `archivo_ruta`   varchar(255) NOT NULL,
  `duracion`       time         DEFAULT NULL,
  `genero`         varchar(50)  DEFAULT NULL,
  `reproducciones` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_cancion`),
  KEY `idx_cancion_album` (`id_album`),
  CONSTRAINT `fk_cancion_album`
    FOREIGN KEY (`id_album`) REFERENCES `albumes` (`id_album`)
    ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2;

-- --------------------------------------------------------
-- Tabla: `creditos_tecnicos`  (depende de: canciones)
-- --------------------------------------------------------
CREATE TABLE `creditos_tecnicos` (
  `id_credito`         int(11)      NOT NULL AUTO_INCREMENT,
  `id_cancion`         int(11)      NOT NULL,
  `nombre_profesional` varchar(150) NOT NULL,
  `rol`                enum('productor','ingeniero_mezcla','masterización','compositor','otro') DEFAULT NULL,
  PRIMARY KEY (`id_credito`),
  KEY `idx_credito_cancion` (`id_cancion`),
  CONSTRAINT `fk_credito_cancion`
    FOREIGN KEY (`id_cancion`) REFERENCES `canciones` (`id_cancion`)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Tabla: `transacciones`  (depende de: usuarios, artistas)
-- --------------------------------------------------------
CREATE TABLE `transacciones` (
  `id_pago`             int(11)       NOT NULL AUTO_INCREMENT,
  `id_usuario_emisor`   int(11)       NOT NULL,
  `id_artista_receptor` int(11)       NOT NULL,
  `importe_total`       decimal(10,2) NOT NULL,
  `iva_aplicado`        decimal(10,2) DEFAULT NULL,
  `fecha_pago`          datetime      NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pago`),
  KEY `idx_transaccion_emisor`   (`id_usuario_emisor`),
  KEY `idx_transaccion_receptor` (`id_artista_receptor`),
  CONSTRAINT `fk_transaccion_emisor`
    FOREIGN KEY (`id_usuario_emisor`)   REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `fk_transaccion_receptor`
    FOREIGN KEY (`id_artista_receptor`) REFERENCES `artistas` (`id_artista`)
) ENGINE=InnoDB;

-- ============================================================
-- INSERCIÓN DE DATOS
-- ============================================================

INSERT INTO `usuarios`
  (`id_usuario`, `email`, `contrasena`, `rol`, `consentimiento_rgpd`, `fecha_registro`, `banned`, `recomendaciones_activas`)
VALUES
  (1, 'williamf.heraim@gmail.com', '$2y$10$QZC9THNeCsNLvdeoNizhC.KNIDm7ogJI.3Ukt26eqthxWTi5gTmwm', 'user',    1, '2026-05-20 05:02:42', 0, 1),
  (3, 'williamf.heraim@proton.me', '$2y$10$IbAGpIEfmzuLEVMSFbbTjevfBE83MC8GnwrvszdXIBqWzK2EndqmK', 'artista', 1, '2026-05-20 05:03:57', 0, 1),
  (4, 'admin@soundplay.com',       '$2y$10$3YKsEj.CFLWjuhrj1hAjNu/DVyHseOGw/qyGCFKraXUEgqjVozQw.', 'admin',   1, '2026-05-20 05:19:47', 0, 1);

INSERT INTO `artistas`
  (`id_artista`, `nombre_artistico`, `bio_extended`, `localidad`, `foto_perfil`, `espacio_maximo`)
VALUES
  (3, 'williamf.heraim', 'Sin biografía.', 'Sin especificar', 'assets/img/default-profile.png', 104857600);

INSERT INTO `albumes`
  (`id_album`, `id_artista`, `titulo`, `portada_ruta`, `fecha_publicacion`)
VALUES
  (1, 3, 'LoveDead', 'uploads/portadas/portada_6a0f0d09e375a3.33666901.png', '2026-05-21');

INSERT INTO `canciones`
  (`id_cancion`, `id_album`, `titulo`, `archivo_ruta`, `duracion`, `genero`, `reproducciones`)
VALUES
  (1, 1, 'Hola', 'uploads/canciones/cancion_6a0f0e54b9c9e3.19123793.mp3', '00:05:32', 'RAP', 0);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;