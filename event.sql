CREATE TABLE IF NOT EXISTS `slot_events` (
  `event_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `image_path` VARCHAR(500) NOT NULL DEFAULT '',
  `start_at` DATETIME NOT NULL,
  `end_at` DATETIME NOT NULL,
  `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_id`),
  KEY `idx_slot_events_visible_period` (`is_visible`, `start_at`, `end_at`),
  KEY `idx_slot_events_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
