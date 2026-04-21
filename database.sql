-- 照片墙数据库结构
-- 导入方式：宝塔面板 → 数据库 → 导入 → 上传此文件

CREATE TABLE IF NOT EXISTS `photos` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `url` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `settings` (
  `key` VARCHAR(100) NOT NULL PRIMARY KEY,
  `value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 默认数据
INSERT INTO `settings` (`key`, `value`) VALUES
('site_title', '我的照片墙'),
('site_subtitle', '记录美好瞬间'),
('site_background', '#0d0d1a');
