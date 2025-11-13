<?php
// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; // Add your MySQL password here if set
$dbName = 'hobbies';

// List of required tables
$requiredTables = [
    'tb_user',
    'tb_category',
    'tb_post',
    'tb_user_post',
    'tb_user_gallery',
    'tb_user_category',
    'tb_liked',
    'tb_install',
    'tb_follow',
    'tb_feedback',
    'tb_download',
    'tb_comment',
    'tb_userlogin'
];

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking for missing tables in database: $dbName\n";
    echo "========================================\n";
    
    // Get existing tables
    $stmt = $pdo->query("SHOW TABLES");
    $existingTables = [];
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $existingTables[] = $row[0];
    }
    
    // Check for missing tables
    $missingTables = array_diff($requiredTables, $existingTables);
    
    if (empty($missingTables)) {
        echo "All required tables exist in the database.\n";
    } else {
        echo "The following tables are missing:\n";
        foreach ($missingTables as $table) {
            echo "- $table\n";
        }
        
        echo "\nTo create the missing tables, you can use the following SQL:\n\n";
        
        // Generate SQL to create missing tables
        $createStatements = [
            'tb_user_gallery' => "CREATE TABLE IF NOT EXISTS `tb_user_gallery` (
                `id_user_gallery` int(11) NOT NULL AUTO_INCREMENT,
                `id_user` int(11) NOT NULL,
                `image` text DEFAULT NULL,
                `status` smallint(1) NOT NULL DEFAULT 1,
                `date_created` datetime NOT NULL,
                `date_updated` datetime DEFAULT NULL,
                `flag` smallint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_user_gallery`),
                KEY `idx_user` (`id_user`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            
            'tb_user_category' => "CREATE TABLE IF NOT EXISTS `tb_user_category` (
                `id_user_category` int(11) NOT NULL AUTO_INCREMENT,
                `id_user` int(11) NOT NULL,
                `id_category` int(11) NOT NULL,
                `status` smallint(1) NOT NULL DEFAULT 1,
                `date_created` datetime NOT NULL,
                `date_updated` datetime DEFAULT NULL,
                `flag` smallint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_user_category`),
                KEY `idx_user` (`id_user`),
                KEY `idx_category` (`id_category`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            
            'tb_liked' => "CREATE TABLE IF NOT EXISTS `tb_liked` (
                `id_liked` int(11) NOT NULL AUTO_INCREMENT,
                `id_user` int(11) NOT NULL,
                `id_post` int(11) NOT NULL,
                `status` smallint(1) NOT NULL DEFAULT 1,
                `date_created` datetime NOT NULL,
                `date_updated` datetime DEFAULT NULL,
                `flag` smallint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_liked`),
                KEY `idx_user` (`id_user`),
                KEY `idx_post` (`id_post`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            
            'tb_install' => "CREATE TABLE IF NOT EXISTS `tb_install` (
                `id_install` int(11) NOT NULL AUTO_INCREMENT,
                `os_platform` varchar(50) DEFAULT NULL,
                `os_version` varchar(50) DEFAULT NULL,
                `device_model` varchar(100) DEFAULT NULL,
                `device_uuid` varchar(100) DEFAULT NULL,
                `app_version` varchar(20) DEFAULT NULL,
                `push_id` varchar(255) DEFAULT NULL,
                `token_fcm` text DEFAULT NULL,
                `token_forgot` varchar(100) DEFAULT NULL,
                `date_created` datetime NOT NULL,
                `date_updated` datetime DEFAULT NULL,
                `flag` smallint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_install`),
                KEY `idx_token_forgot` (`token_forgot`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            
            'tb_follow' => "CREATE TABLE IF NOT EXISTS `tb_follow` (
                `id_follow` int(11) NOT NULL AUTO_INCREMENT,
                `id_user` int(11) NOT NULL,
                `id_user_following` int(11) NOT NULL,
                `status` smallint(1) NOT NULL DEFAULT 1,
                `date_created` datetime NOT NULL,
                `date_updated` datetime DEFAULT NULL,
                `flag` smallint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_follow`),
                KEY `idx_user` (`id_user`),
                KEY `idx_user_following` (`id_user_following`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            
            'tb_feedback' => "CREATE TABLE IF NOT EXISTS `tb_feedback` (
                `id_feedback` int(11) NOT NULL AUTO_INCREMENT,
                `id_user` int(11) NOT NULL,
                `subject` varchar(255) DEFAULT NULL,
                `message` text DEFAULT NULL,
                `status` smallint(1) NOT NULL DEFAULT 1,
                `date_created` datetime NOT NULL,
                `date_updated` datetime DEFAULT NULL,
                `flag` smallint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_feedback`),
                KEY `idx_user` (`id_user`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            
            'tb_download' => "CREATE TABLE IF NOT EXISTS `tb_download` (
                `id_download` int(11) NOT NULL AUTO_INCREMENT,
                `id_user` int(11) NOT NULL,
                `id_post` int(11) NOT NULL,
                `date_created` datetime NOT NULL,
                `date_updated` datetime DEFAULT NULL,
                `flag` smallint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_download`),
                KEY `idx_user` (`id_user`),
                KEY `idx_post` (`id_post`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            
            'tb_comment' => "CREATE TABLE IF NOT EXISTS `tb_comment` (
                `id_comment` int(11) NOT NULL AUTO_INCREMENT,
                `id_user` int(11) NOT NULL,
                `id_post` int(11) NOT NULL,
                `comment` text DEFAULT NULL,
                `status` smallint(1) NOT NULL DEFAULT 1,
                `date_created` datetime NOT NULL,
                `date_updated` datetime DEFAULT NULL,
                `flag` smallint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_comment`),
                KEY `idx_user` (`id_user`),
                KEY `idx_post` (`id_post`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            
            'tb_userlogin' => "CREATE TABLE IF NOT EXISTS `tb_userlogin` (
                `id_userlogin` int(11) NOT NULL AUTO_INCREMENT,
                `id_user` int(11) NOT NULL,
                `token` text DEFAULT NULL,
                `ip_address` varchar(50) DEFAULT NULL,
                `user_agent` text DEFAULT NULL,
                `date_created` datetime NOT NULL,
                `date_updated` datetime DEFAULT NULL,
                `flag` smallint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_userlogin`),
                KEY `idx_user` (`id_user`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        ];
        
        foreach ($missingTables as $table) {
            if (isset($createStatements[$table])) {
                echo "-- SQL to create $table table\n";
                echo $createStatements[$table] . "\n\n";
            } else {
                echo "-- Note: No create statement available for $table\n\n";
            }
        }
    }
    
    echo "========================================\n";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>
