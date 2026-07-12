CREATE TABLE IF NOT EXISTS `site_settings` (
  `setting_key`   VARCHAR(120) NOT NULL PRIMARY KEY,
  `setting_value` TEXT         NULL,
  `updated_at`    DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
