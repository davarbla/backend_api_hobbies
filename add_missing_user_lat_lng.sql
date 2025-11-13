-- Add missing lat/lng columns to tb_user table
ALTER TABLE `tb_user` 
ADD COLUMN `lat` varchar(100) DEFAULT NULL AFTER `message`,
ADD COLUMN `lng` varchar(100) DEFAULT NULL AFTER `lat`;

-- Add indexes for better performance
ALTER TABLE `tb_user` ADD INDEX `idx_lat` (`lat`);
ALTER TABLE `tb_user` ADD INDEX `idx_lng` (`lng`);
