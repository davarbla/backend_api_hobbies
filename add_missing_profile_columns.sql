-- Add missing columns to tb_user table for profile update functionality
ALTER TABLE tb_user 
ADD COLUMN height VARCHAR(10) DEFAULT '0',
ADD COLUMN weight VARCHAR(10) DEFAULT '0',
ADD COLUMN position VARCHAR(50) DEFAULT '0',
ADD COLUMN protection VARCHAR(20) DEFAULT '0',
ADD COLUMN relationship VARCHAR(20) DEFAULT '0',
ADD COLUMN bodyColor VARCHAR(20) DEFAULT '0',
ADD COLUMN bodyShape VARCHAR(20) DEFAULT '0',
ADD COLUMN hair VARCHAR(20) DEFAULT '0';

-- Verify the columns were added
SHOW COLUMNS FROM tb_user;
