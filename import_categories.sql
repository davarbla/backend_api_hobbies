-- First, let's check if the table exists and create it if it doesn't
CREATE TABLE IF NOT EXISTS `tb_category` (
  `id_category` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `total_interest` int(11) DEFAULT '0',
  `total_post` int(11) DEFAULT '0',
  `total_like` int(11) DEFAULT '0',
  `total_trivia` int(11) DEFAULT '0',
  `flag` int(11) DEFAULT '1',
  `status` int(11) DEFAULT '1',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `id_category_up` int(11) DEFAULT '0',
  `private` int(11) DEFAULT '0',
  `group` int(11) DEFAULT '0',
  `latitude` varchar(255) DEFAULT '',
  `location` varchar(255) DEFAULT '',
  `id_owner` int(11) DEFAULT NULL,
  `fun` int(11) DEFAULT '0',
  `subscribe_fcm` varchar(255) DEFAULT '',
  `lat` float DEFAULT '0',
  `lng` float DEFAULT '0',
  `country` varchar(2) DEFAULT '',
  PRIMARY KEY (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Truncate the table to remove any existing data
TRUNCATE TABLE `tb_category`;

-- Load the data from CSV
LOAD DATA LOCAL INFILE 'c:/Workspace/hobbies/AllSource_Code_HobbiesApp_v102/backend_api_hobbies/root/mysql/scripts/tb_category_PROD.csv'
INTO TABLE `tb_category`
FIELDS TERMINATED BY ';' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(`id_category`, `title`, `description`, `image`, `total_interest`, `total_post`, `total_like`, `total_trivia`, 
 `flag`, `status`, `date_created`, `date_updated`, `id_category_up`, `private`, `group`, `latitude`, `location`, 
 `id_owner`, `fun`, `subscribe_fcm`, `lat`, `lng`, `country`);

-- Verify the import
SELECT COUNT(*) AS total_categories_imported FROM `tb_category`;
