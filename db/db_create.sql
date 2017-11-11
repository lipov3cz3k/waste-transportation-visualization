-- --------------------------------------------------------
-- Hostitel:                     127.0.0.1
-- Verze serveru:                5.7.20-0ubuntu0.16.04.1 - (Ubuntu)
-- OS serveru:                   Linux
-- HeidiSQL Verze:               9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Exportování struktury pro tabulka hamsik.edge
CREATE TABLE IF NOT EXISTS `edge` (
  `edge_id` int(11) NOT NULL AUTO_INCREMENT,
  `node_a` int(11) NOT NULL,
  `node_b` int(11) NOT NULL,
  `scenario_id` int(11) NOT NULL,
  `load_ab` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`edge_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1059972 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Export dat nebyl vybrán.
-- Exportování struktury pro tabulka hamsik.edge_upscenario
CREATE TABLE IF NOT EXISTS `edge_upscenario` (
  `edge_upscenario_id` int(11) NOT NULL AUTO_INCREMENT,
  `node_a` int(11) NOT NULL,
  `node_b` int(11) NOT NULL,
  `upscenario_id` int(11) NOT NULL,
  `load_ab` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `length` float NOT NULL,
  `load_capacity` float NOT NULL,
  `load_height` float NOT NULL,
  `aadt_max` int(11) NOT NULL,
  `aadt_mean` int(11) NOT NULL,
  `tv_mean` int(11) NOT NULL,
  `accidents` float NOT NULL,
  PRIMARY KEY (`edge_upscenario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16792 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Export dat nebyl vybrán.
-- Exportování struktury pro tabulka hamsik.modul
CREATE TABLE IF NOT EXISTS `modul` (
  `modul_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `info` varchar(512) COLLATE utf8_czech_ci NOT NULL,
  `production` varchar(1024) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`modul_id`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Export dat nebyl vybrán.
-- Exportování struktury pro tabulka hamsik.node
CREATE TABLE IF NOT EXISTS `node` (
  `node_id` int(11) NOT NULL AUTO_INCREMENT,
  `id_original` int(11) NOT NULL,
  `name` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `type` int(11) NOT NULL,
  `modul_id` int(11) NOT NULL,
  `longitude` decimal(9,6) NOT NULL,
  `latitude` decimal(9,6) NOT NULL,
  `country` varchar(128) COLLATE utf8_czech_ci NOT NULL,
  `region` varchar(128) COLLATE utf8_czech_ci NOT NULL,
  `info` varchar(128) COLLATE utf8_czech_ci NOT NULL,
  `production` varchar(1024) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`node_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5708 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Export dat nebyl vybrán.
-- Exportování struktury pro tabulka hamsik.permission
CREATE TABLE IF NOT EXISTS `permission` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `modul_id` int(11) NOT NULL,
  `p_a` int(11) NOT NULL,
  `p_b` int(11) NOT NULL,
  `p_c` int(11) NOT NULL,
  PRIMARY KEY (`permission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Export dat nebyl vybrán.
-- Exportování struktury pro tabulka hamsik.scenario
CREATE TABLE IF NOT EXISTS `scenario` (
  `scenario_id` int(11) NOT NULL AUTO_INCREMENT,
  `upscenario_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`scenario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2717 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Export dat nebyl vybrán.
-- Exportování struktury pro tabulka hamsik.upscenario
CREATE TABLE IF NOT EXISTS `upscenario` (
  `upscenario_id` int(11) NOT NULL AUTO_INCREMENT,
  `modul_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `load_structure` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`upscenario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Export dat nebyl vybrán.
-- Exportování struktury pro tabulka hamsik.user
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `password` varchar(48) COLLATE utf8_czech_ci NOT NULL,
  `type` tinyint(4) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Export dat nebyl vybrán.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
