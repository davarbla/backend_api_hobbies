-- Set the database
USE hobbies;

-- Disable foreign key checks to avoid constraint errors during import
SET FOREIGN_KEY_CHECKS = 0;

-- Truncate the table if it already has data
-- WARNING: This will delete all existing data in tb_category
-- TRUNCATE TABLE tb_category;

-- Import data from CSV file (REPLACE existing duplicates)
LOAD DATA INFILE 'c:/Workspace/hobbies/AllSource_Code_HobbiesApp_v102/backend_api_hobbies/root/mysql/BD_backup/tb_category.csv' 
REPLACE INTO TABLE tb_category 
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"' 
LINES TERMINATED BY '\r\n'  -- or '\n' if the file uses Unix line endings
IGNORE 1 LINES  -- Skip header row if it exists
(
    id_category, title, description, image, total_interest, 
    total_post, total_like, total_trivia, flag, status, 
    date_created, date_updated, id_category_up, private, `group`, 
    latitude, location, id_owner, fun, subscribe_fcm, 
    lat, lng, country
);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verify the import
SELECT COUNT(*) AS 'Number of categories imported' FROM tb_category;
