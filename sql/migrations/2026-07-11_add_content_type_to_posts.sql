ALTER TABLE `posts`
  ADD COLUMN `content_type` ENUM('article','news') NOT NULL DEFAULT 'article' AFTER `slug`,
  ADD INDEX `idx_posts_content_status_date` (`content_type`, `status`, `publish_date`);
