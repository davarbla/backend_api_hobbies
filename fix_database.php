<?php
// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; // Add your MySQL password here if set
$dbName = 'hobbies';

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Fixing database structure...\n";
    echo "========================================\n";
    
    // Add missing columns to tb_user
    try {
        $pdo->exec("ALTER TABLE `tb_user` 
            ADD COLUMN IF NOT EXISTS `public` smallint(1) NOT NULL DEFAULT '1' AFTER `date_updated",
            "ADD COLUMN IF NOT EXISTS `ugly` smallint(1) NOT NULL DEFAULT '0' AFTER `public`,
            ADD COLUMN IF NOT EXISTS `image2` text DEFAULT NULL AFTER `ugly`,
            ADD COLUMN IF NOT EXISTS `age` int(11) DEFAULT NULL AFTER `image2`,
            ADD COLUMN IF NOT EXISTS `message` text DEFAULT NULL AFTER `age`");
        echo "Added missing columns to tb_user\n";
    } catch (PDOException $e) {
        echo "Error adding columns to tb_user: " . $e->getMessage() . "\n";
    }
    
    // Add missing columns to tb_category
    try {
        $pdo->exec("ALTER TABLE `tb_category` 
            ADD COLUMN IF NOT EXISTS `name_category` varchar(100) DEFAULT NULL AFTER `id_category`,
            ADD COLUMN IF NOT EXISTS `id_category_up` int(11) DEFAULT NULL AFTER `date_updated`,
            ADD COLUMN IF NOT EXISTS `private` smallint(1) NOT NULL DEFAULT '0' AFTER `id_category_up`,
            ADD COLUMN IF NOT EXISTS `group` smallint(1) NOT NULL DEFAULT '0' AFTER `private`,
            ADD COLUMN IF NOT EXISTS `latitude` varchar(100) DEFAULT NULL AFTER `group`,
            ADD COLUMN IF NOT EXISTS `location` varchar(255) DEFAULT NULL AFTER `latitude`,
            ADD COLUMN IF NOT EXISTS `id_owner` int(11) DEFAULT NULL AFTER `location`,
            ADD COLUMN IF NOT EXISTS `fun` smallint(1) NOT NULL DEFAULT '0' AFTER `id_owner`,
            ADD COLUMN IF NOT EXISTS `subscribe_fcm` varchar(50) DEFAULT NULL AFTER `fun`,
            ADD COLUMN IF NOT EXISTS `lat` varchar(100) DEFAULT NULL AFTER `subscribe_fcm`,
            ADD COLUMN IF NOT EXISTS `lng` varchar(100) DEFAULT NULL AFTER `lat`,
            ADD COLUMN IF NOT EXISTS `country` varchar(5) DEFAULT 'US' AFTER `lng`");
        echo "Added missing columns to tb_category\n";
    } catch (PDOException $e) {
        echo "Error adding columns to tb_category: " . $e->getMessage() . "\n";
    }
    
    // Add missing columns to tb_post
    try {
        $pdo->exec("ALTER TABLE `tb_post` 
            ADD COLUMN IF NOT EXISTS `content` text DEFAULT NULL AFTER `title`,
            ADD COLUMN IF NOT EXISTS `subscribe_fcm` varchar(50) DEFAULT NULL AFTER `image3`,
            ADD COLUMN IF NOT EXISTS `address_detail` text DEFAULT NULL AFTER `subscribe_fcm`,
            ADD COLUMN IF NOT EXISTS `address` varchar(255) DEFAULT NULL AFTER `address_detail`,
            ADD COLUMN IF NOT EXISTS `bring` text DEFAULT NULL AFTER `address`,
            ADD COLUMN IF NOT EXISTS `max_people` int(11) DEFAULT NULL AFTER `bring`,
            ADD COLUMN IF NOT EXISTS `price` decimal(10,2) DEFAULT NULL AFTER `max_people`,
            ADD COLUMN IF NOT EXISTS `start_date` datetime DEFAULT NULL AFTER `price`,
            ADD COLUMN IF NOT EXISTS `end_date` datetime DEFAULT NULL AFTER `start_date`,
            ADD COLUMN IF NOT EXISTS `age_min` int(11) DEFAULT NULL AFTER `end_date`,
            ADD COLUMN IF NOT EXISTS `age_max` int(11) DEFAULT NULL AFTER `age_min`,
            ADD COLUMN IF NOT EXISTS `fun` smallint(1) NOT NULL DEFAULT '0' AFTER `age_max`,
            ADD COLUMN IF NOT EXISTS `lat` varchar(100) DEFAULT NULL AFTER `fun`,
            ADD COLUMN IF NOT EXISTS `lng` varchar(100) DEFAULT NULL AFTER `lat`,
            ADD COLUMN IF NOT EXISTS `country` varchar(5) DEFAULT 'US' AFTER `lng`,
            ADD COLUMN IF NOT EXISTS `cancell` smallint(1) NOT NULL DEFAULT '0' AFTER `country`");
        echo "Added missing columns to tb_post\n";
    } catch (PDOException $e) {
        echo "Error adding columns to tb_post: " . $e->getMessage() . "\n";
    }
    
    // Create tb_user_post table if it doesn't exist
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `tb_user_post` (
            `id_user_post` int(11) NOT NULL AUTO_INCREMENT,
            `id_user` int(11) NOT NULL,
            `id_post` int(11) NOT NULL,
            `status` smallint(1) NOT NULL DEFAULT '1',
            `date_created` datetime NOT NULL,
            `date_updated` datetime DEFAULT NULL,
            `flag` smallint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`id_user_post`),
            KEY `idx_user` (`id_user`),
            KEY `idx_post` (`id_post`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        echo "Created tb_user_post table\n";
    } catch (PDOException $e) {
        echo "Error creating tb_user_post table: " . $e->getMessage() . "\n";
    }
    
    echo "========================================\n";
    echo "Database structure update completed.\n";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>
