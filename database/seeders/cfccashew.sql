-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for cfccashew
CREATE DATABASE IF NOT EXISTS `cfccashew` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `cfccashew`;

-- Dumping structure for table cfccashew.bills
CREATE TABLE IF NOT EXISTS `bills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bill_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seller` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_bills_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping structure for table cfccashew.containers
CREATE TABLE IF NOT EXISTS `containers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` bigint unsigned NOT NULL,
  `truck` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `container_number` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity_of_bags` int unsigned DEFAULT NULL,
  `w_jute_bag` decimal(4,2) DEFAULT '1.00',
  `w_total` int unsigned DEFAULT NULL,
  `w_truck` int unsigned DEFAULT NULL,
  `w_container` int unsigned DEFAULT NULL,
  `w_gross` int unsigned DEFAULT NULL,
  `w_dunnage_dribag` int unsigned DEFAULT NULL,
  `w_tare` decimal(10,2) DEFAULT NULL,
  `w_net` decimal(10,2) DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `containers_bill_id_foreign` (`bill_id`),
  KEY `idx_containers_created_at` (`created_at`),
  KEY `idx_containers_container_number` (`container_number`),
  CONSTRAINT `containers_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`),
  CONSTRAINT `chk_container_iso` CHECK (((`container_number` is null) or regexp_like(`container_number`,_utf8mb4'^[A-Z]{4}[0-9]{7}$')))
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table cfccashew.cutting_tests
CREATE TABLE IF NOT EXISTS `cutting_tests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` bigint unsigned NOT NULL,
  `container_id` bigint unsigned DEFAULT NULL,
  `type` smallint NOT NULL COMMENT '1-final sample first cut/ 2-final sample second cut/ 3-final sample third cut/4-container cut',
  `moisture` decimal(4,2) DEFAULT NULL,
  `sample_weight` smallint unsigned NOT NULL DEFAULT '1000',
  `nut_count` smallint unsigned DEFAULT NULL,
  `w_reject_nut` smallint unsigned DEFAULT NULL,
  `w_defective_nut` smallint unsigned DEFAULT NULL,
  `w_defective_kernel` smallint unsigned DEFAULT NULL,
  `w_good_kernel` smallint unsigned DEFAULT NULL,
  `w_sample_after_cut` smallint unsigned DEFAULT NULL,
  `outturn_rate` decimal(5,2) DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cutting_tests_bill_id_foreign` (`bill_id`),
  KEY `cutting_tests_container_id_foreign` (`container_id`),
  KEY `idx_cutting_tests_created_at` (`created_at`),
  CONSTRAINT `cutting_tests_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`),
  CONSTRAINT `cutting_tests_container_id_foreign` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`),
  CONSTRAINT `chk_moisture_range` CHECK (((`moisture` is null) or ((`moisture` >= 0) and (`moisture` <= 100)))),
  CONSTRAINT `chk_outturn_range` CHECK (((`outturn_rate` is null) or ((`outturn_rate` >= 0) and (`outturn_rate` <= 60))))
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
