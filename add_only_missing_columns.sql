-- Add missing columns to tb_category table
ALTER TABLE `tb_category` 
ADD COLUMN `country` varchar(5) DEFAULT 'FR' AFTER `id_owner`,
ADD COLUMN `latitude` varchar(100) DEFAULT NULL AFTER `country`,
ADD COLUMN `lat` varchar(100) DEFAULT NULL AFTER `latitude`,
ADD COLUMN `lng` varchar(100) DEFAULT NULL AFTER `lat`,
ADD COLUMN `fun` smallint(1) NOT NULL DEFAULT '0' AFTER `lng`;

-- Add missing columns to tb_user table
ALTER TABLE `tb_user` 
ADD COLUMN `public` smallint(1) NOT NULL DEFAULT '1' AFTER `date_updated`,
ADD COLUMN `ugly` smallint(1) NOT NULL DEFAULT '0' AFTER `public`,
ADD COLUMN `age` int(11) DEFAULT NULL AFTER `ugly`,
ADD COLUMN `message` text DEFAULT NULL AFTER `age`;

-- Add indexes for better performance
ALTER TABLE `tb_category` ADD INDEX `idx_country` (`country`);
ALTER TABLE `tb_category` ADD INDEX `idx_lat` (`lat`);
ALTER TABLE `tb_category` ADD INDEX `idx_lng` (`lng`);
ALTER TABLE `tb_category` ADD INDEX `idx_fun` (`fun`);

ALTER TABLE `tb_user` ADD INDEX `idx_public` (`public`);
ALTER TABLE `tb_user` ADD INDEX `idx_ugly` (`ugly`);
ALTER TABLE `tb_user` ADD INDEX `idx_age` (`age`);
