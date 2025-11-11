-- Add only the missing columns to tb_category table
ALTER TABLE `tb_category` 
ADD COLUMN IF NOT EXISTS `private` smallint(1) NOT NULL DEFAULT '0' AFTER `id_category_up`,
ADD COLUMN IF NOT EXISTS `group` smallint(1) NOT NULL DEFAULT '0' AFTER `private`,
ADD COLUMN IF NOT EXISTS `location` varchar(255) DEFAULT NULL AFTER `latitude`;
