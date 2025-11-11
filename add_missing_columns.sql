-- Add missing columns to tb_category table
ALTER TABLE `tb_category` 
ADD COLUMN `id_category_up` int(11) DEFAULT NULL AFTER `date_updated`,
ADD COLUMN `private` smallint(1) NOT NULL DEFAULT '0' AFTER `id_category_up`,
ADD COLUMN `group` smallint(1) NOT NULL DEFAULT '0' AFTER `private`,
ADD COLUMN `latitude` varchar(100) DEFAULT NULL AFTER `group`,
ADD COLUMN `location` varchar(255) DEFAULT NULL AFTER `latitude`,
ADD COLUMN `id_owner` int(11) DEFAULT NULL AFTER `location`,
ADD COLUMN `fun` smallint(1) NOT NULL DEFAULT '0' AFTER `id_owner`,
ADD COLUMN `subscribe_fcm` varchar(50) DEFAULT NULL AFTER `fun`,
ADD COLUMN `lat` varchar(100) DEFAULT NULL AFTER `subscribe_fcm`,
ADD COLUMN `lng` varchar(100) DEFAULT NULL AFTER `lat`,
ADD COLUMN `country` varchar(5) DEFAULT 'FR' AFTER `lng`;

-- Add missing columns to tb_post table
ALTER TABLE `tb_post` 
ADD COLUMN `subscribe_fcm` varchar(50) DEFAULT NULL AFTER `image3`,
ADD COLUMN `address_detail` text DEFAULT NULL AFTER `subscribe_fcm`,
ADD COLUMN `address` varchar(255) DEFAULT NULL AFTER `address_detail`,
ADD COLUMN `bring` text DEFAULT NULL AFTER `address`,
ADD COLUMN `max_people` int(11) DEFAULT NULL AFTER `bring`,
ADD COLUMN `price` decimal(10,2) DEFAULT NULL AFTER `max_people`,
ADD COLUMN `start_date` datetime DEFAULT NULL AFTER `price`,
ADD COLUMN `end_date` datetime DEFAULT NULL AFTER `start_date`,
ADD COLUMN `age_min` int(11) DEFAULT NULL AFTER `end_date`,
ADD COLUMN `age_max` int(11) DEFAULT NULL AFTER `age_min`,
ADD COLUMN `fun` smallint(1) NOT NULL DEFAULT '0' AFTER `age_max`,
ADD COLUMN `lat` varchar(100) DEFAULT NULL AFTER `fun`,
ADD COLUMN `lng` varchar(100) DEFAULT NULL AFTER `lat`,
ADD COLUMN `country` varchar(5) DEFAULT 'FR' AFTER `lng`,
ADD COLUMN `cancell` smallint(1) NOT NULL DEFAULT '0' AFTER `country`;

-- Add missing columns to tb_user table
ALTER TABLE `tb_user` 
ADD COLUMN `public` smallint(1) NOT NULL DEFAULT '1' AFTER `date_updated`,
ADD COLUMN `ugly` smallint(1) NOT NULL DEFAULT '0' AFTER `public`,
ADD COLUMN `image2` text DEFAULT NULL AFTER `ugly`,
ADD COLUMN `age` int(11) DEFAULT NULL AFTER `image2`,
ADD COLUMN `message` text DEFAULT NULL AFTER `age`;

-- Add indexes for better performance
ALTER TABLE `tb_category` ADD INDEX `idx_category_up` (`id_category_up`);
ALTER TABLE `tb_category` ADD INDEX `idx_private` (`private`);
ALTER TABLE `tb_category` ADD INDEX `idx_group` (`group`);
ALTER TABLE `tb_category` ADD INDEX `idx_location` (`location`);
ALTER TABLE `tb_category` ADD INDEX `idx_owner` (`id_owner`);
ALTER TABLE `tb_category` ADD INDEX `idx_fun` (`fun`);
ALTER TABLE `tb_category` ADD INDEX `idx_subscribe_fcm` (`subscribe_fcm`);
ALTER TABLE `tb_category` ADD INDEX `idx_lat` (`lat`);
ALTER TABLE `tb_category` ADD INDEX `idx_lng` (`lng`);
ALTER TABLE `tb_category` ADD INDEX `idx_country` (`country`);

ALTER TABLE `tb_post` ADD INDEX `idx_start_date` (`start_date`);
ALTER TABLE `tb_post` ADD INDEX `idx_end_date` (`end_date`);
ALTER TABLE `tb_post` ADD INDEX `idx_fun_post` (`fun`);
ALTER TABLE `tb_post` ADD INDEX `idx_country_post` (`country`);

ALTER TABLE `tb_user` ADD INDEX `idx_public` (`public`);
ALTER TABLE `tb_user` ADD INDEX `idx_ugly` (`ugly`);
ALTER TABLE `tb_user` ADD INDEX `idx_age` (`age`);
