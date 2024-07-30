-- Adminer 4.8.1 MySQL 8.3.0 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `sendmail`;
CREATE TABLE `sendmail` (
  `id` int NOT NULL AUTO_INCREMENT,
  `req_count` int NOT NULL,
  `timestamp` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(128) NOT NULL,
  `token` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `sendmail` (`id`, `req_count`, `timestamp`, `ip`, `token`) VALUES
(2,	2,	'2024-02-29 14:01:04',	'10.11.2.75',	'$2y$10$xOGdx9NR0nTU763hj2j.g.TsgkGWYW06YdNq2z2xUOxcWFbEB5WsG');

-- 2024-02-29 14:01:26
