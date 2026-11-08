-- 게임포인트와 그누보드 포인트를 통합할 원장
-- 현재 호환 소스에서는 zetyx_member_table.gamepoint를 단일 잔액으로 사용합니다.
CREATE TABLE IF NOT EXISTS `slot_point_ledger` (
  `ledger_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(80) NOT NULL,
  `actor_user_id` VARCHAR(80) NOT NULL,
  `amount` BIGINT NOT NULL,
  `balance_after` BIGINT UNSIGNED NOT NULL,
  `point_type` ENUM('daily_charge', 'admin_charge', 'admin_gift', 'game') NOT NULL,
  `reference_key` VARCHAR(190) NOT NULL,
  `memo` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ledger_id`),
  UNIQUE KEY `uq_slot_point_reference` (`reference_key`),
  KEY `idx_slot_point_user_created` (`user_id`, `created_at`),
  KEY `idx_slot_point_actor_created` (`actor_user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `slot_daily_point_claims` (
  `claim_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(80) NOT NULL,
  `claim_date` DATE NOT NULL,
  `amount` BIGINT UNSIGNED NOT NULL DEFAULT 50000,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`claim_id`),
  UNIQUE KEY `uq_slot_daily_claim` (`user_id`, `claim_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
