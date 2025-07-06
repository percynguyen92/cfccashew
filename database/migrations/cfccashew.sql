CREATE TABLE `bills` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `billNumber` varchar(20) NOT NULL,
  `seller` varchar(255),
  `buyer` varchar(255) DEFAULT null,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT null,
  `deleted_at` timestamp DEFAULT null
);

CREATE TABLE `containers` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `bill_id` integer NOT NULL,
  `truck` varchar(20),
  `container_number` varchar(11),
  `quantity_of_bags` integer,
  `w_jute_bag` decimal(4,2),
  `w_total` integer,
  `w_truck` integer,
  `w_container` integer,
  `w_gross` integer,
  `w_dunnage_dribag` integer,
  `w_tare` decimal(10,2),
  `w_net` decimal(10,2) DEFAULT null,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT null,
  `deleted_at` timestamp DEFAULT null
);

CREATE TABLE `cutting_tests` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `bill_id` integer NOT NULL,
  `container_id` integer NULL,
  `type` smallint NOT NULL COMMENT '1-final sample first cut/ 2-final sample second cut/ 3-final sample third cut/4-container cut',
  `moisture` decimal(4,2) DEFAULT null COMMENT 'Độ ẩm (%)',
  `sample_weight` smallint unsigned NOT NULL DEFAULT 1000 COMMENT 'Trọng lượng mẫu (gram)',
  `nut_count` smallint unsigned DEFAULT null COMMENT 'Số hạt trong mẫu',
  `w_reject_nut` smallint unsigned DEFAULT 1 COMMENT 'Trọng lượng hạt lỗi hoàn toàn',
  `w_defective_nut` smallint unsigned DEFAULT null COMMENT 'Trọng lượng hạt lỗi một phần',
  `w_defective_kernel` smallint unsigned DEFAULT null COMMENT 'Trọng lượng nhân lỗi một phần',
  `w_good_kernel` smallint unsigned DEFAULT null COMMENT 'Trọng lượng nhân điều tốt',
  `w_sample_after_cut` smallint unsigned DEFAULT null COMMENT 'Tổng trọng lượng mẫu sau khi cắt',
  `outturn_rate` decimal(5,2) DEFAULT null COMMENT 'Thu hồi nhân (lbs/80kg)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
);

ALTER TABLE `containers` ADD FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`);

ALTER TABLE `cutting_tests` ADD FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`);

ALTER TABLE `cutting_tests` ADD FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`);
