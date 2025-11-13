<?php
// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; // Add your MySQL password here if set
$dbName = 'hobbies';

// Required tables and their columns
$requiredTables = [
    'tb_user' => [
        'id_user', 'email', 'fullname', 'username', 'phone', 'password_user',
        'id_install', 'uid_fcm', 'latitude', 'location', 'country', 'status',
        'flag', 'date_created', 'date_updated', 'subscribe_fcm', 'public',
        'total_post', 'total_like', 'total_comment', 'total_download',
        'total_follower', 'total_following', 'timestamp'
    ],
    'tb_category' => [
        'id_category', 'name_category', 'image', 'status', 'flag',
        'date_created', 'date_updated', 'id_category_up', 'private',
        'group', 'latitude', 'location', 'id_owner', 'fun', 'subscribe_fcm',
        'lat', 'lng', 'country'
    ],
    'tb_post' => [
        'id_post', 'id_user', 'id_category', 'title', 'content', 'image',
        'image2', 'image3', 'status', 'flag', 'date_created', 'date_updated',
        'subscribe_fcm', 'address_detail', 'address', 'bring', 'max_people',
        'price', 'start_date', 'end_date', 'age_min', 'age_max', 'fun',
        'lat', 'lng', 'country', 'cancell'
    ],
    'tb_user_post' => [
        'id_user_post', 'id_user', 'id_post', 'status', 'date_created',
        'date_updated', 'flag'
    ]
];

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking database structure...\n";
    echo "========================================\n";
    
    $allGood = true;
    
    // Check each required table
    foreach ($requiredTables as $table => $columns) {
        echo "Checking table: $table... ";
        
        try {
            // Check if table exists
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() === 0) {
                echo "MISSING TABLE!\n";
                $allGood = false;
                continue;
            }
            
            // Get table columns
            $stmt = $pdo->query("DESCRIBE `$table`");
            $existingColumns = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $existingColumns[] = $row['Field'];
            }
            
            // Check for missing columns
            $missingColumns = array_diff($columns, $existingColumns);
            
            if (!empty($missingColumns)) {
                echo "MISSING COLUMNS: " . implode(', ', $missingColumns) . "\n";
                $allGood = false;
            } else {
                echo "OK\n";
            }
            
        } catch (PDOException $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
            $allGood = false;
        }
    }
    
    echo "========================================\n";
    if ($allGood) {
        echo "Database structure is complete!\n";
    } else {
        echo "WARNING: Some tables or columns are missing.\n";
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>
