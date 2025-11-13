<?php
// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; // Add your MySQL password here if set
$dbName = 'hobbies';

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if the users table exists and has data
    $tables = [
        'tb_user' => 'Users',
        'tb_category' => 'Categories',
        'tb_post' => 'Posts',
        'tb_comment' => 'Comments'
    ];
    
    foreach ($tables as $table => $name) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $count = $pdo->query("SELECT COUNT(*) as count FROM $table")->fetch(PDO::FETCH_ASSOC)['count'];
            echo "✅ $name table exists with $count records\n";
            
            // Show table structure
            echo "   Table structure for $table:\n";
            $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_COLUMN);
            echo "   Columns: " . implode(', ', $columns) . "\n\n";
        } else {
            echo "❌ $name table does not exist\n";
        }
    }
    
    // Check for common issues
    echo "\nChecking for common issues...\n";
    
    // Check if the subscribe_fcm column exists in tb_user
    try {
        $pdo->query("SELECT subscribe_fcm FROM tb_user LIMIT 1");
        echo "✅ subscribe_fcm column exists in tb_user\n";
    } catch (PDOException $e) {
        echo "❌ subscribe_fcm column is missing from tb_user\n";
        
        // Try to add the missing column
        try {
            $pdo->exec("ALTER TABLE tb_user ADD COLUMN subscribe_fcm TINYINT(1) DEFAULT 1");
            echo "✅ Added subscribe_fcm column to tb_user\n";
        } catch (PDOException $e) {
            echo "❌ Failed to add subscribe_fcm column: " . $e->getMessage() . "\n";
        }
    }
    
    // Check if the database has any users
    $userCount = $pdo->query("SELECT COUNT(*) as count FROM tb_user")->fetch(PDO::FETCH_ASSOC)['count'];
    if ($userCount == 0) {
        echo "⚠️  No users found in the database. You may need to register a new user.\n";
    } else {
        echo "✅ Found $userCount users in the database\n";
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}

// Function to execute SQL file
function executeSqlFile($pdo, $file) {
    if (!file_exists($file)) {
        echo "⚠️  SQL file not found: $file\n";
        return false;
    }
    
    $sql = file_get_contents($file);
    $queries = explode(';', $sql);
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            try {
                $pdo->exec($query);
            } catch (PDOException $e) {
                echo "⚠️  Error executing query: " . $e->getMessage() . "\n";
            }
        }
    }
    
    return true;
}
?>
