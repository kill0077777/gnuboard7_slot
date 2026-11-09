CREATE TABLE IF NOT EXISTS `slot_game_settings` (
  `setting_key` VARCHAR(80) NOT NULL,
  `setting_value` VARCHAR(80) NOT NULL,
  `updated_by` VARCHAR(80) NOT NULL DEFAULT '',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `slot_game_settings` (`setting_key`, `setting_value`, `updated_by`) VALUES
  ('bet_min', '20', 'system'),
  ('bet_max', '300', 'system'),
  ('jackpot_min', '3000', 'system'),
  ('jackpot_max', '20000', 'system'),
  ('double_5_chance_bp', '100', 'system'),
  ('double_3_chance_bp', '100', 'system'),
  ('double_2_chance_bp', '200', 'system'),
  ('payout_multiplier_bp', '10000', 'system')
ON DUPLICATE KEY UPDATE `setting_key` = VALUES(`setting_key`);
