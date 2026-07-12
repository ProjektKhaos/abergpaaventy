ALTER TABLE `posts`
  MODIFY COLUMN `content_type` ENUM('article','news','cmt') NOT NULL DEFAULT 'article',
  ADD COLUMN `latitude` DECIMAL(10,7) NULL AFTER `location`,
  ADD COLUMN `longitude` DECIMAL(10,7) NULL AFTER `latitude`;

CREATE TABLE IF NOT EXISTS `post_links` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `post_id`    INT           NOT NULL,
  `label`      VARCHAR(255)  NOT NULL,
  `url`        VARCHAR(2048) NOT NULL,
  `sort_order` INT           NOT NULL DEFAULT 0,
  FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
  INDEX `idx_post_links_post_sort` (`post_id`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
