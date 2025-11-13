<?php
try {
    // Database configuration
    $host = 'localhost';
    $dbname = 'hobbies';
    $username = 'root';
    $password = '';
    
    // Create connection
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Successfully connected to the database.\n";
    
    // Check if tb_user exists and show its structure
    $stmt = $db->query("SHOW TABLES LIKE 'tb_user'");
    if ($stmt->rowCount() > 0) {
        echo "✅ tb_user table exists.\n";
        
        // Show table structure
        echo "\n📋 tb_user table structure:\n";
        $columns = $db->query("SHOW COLUMNS FROM tb_user")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "- {$column['Field']} | {$column['Type']} | {$column['Null']} | {$column['Key']} | {$column['Default']}\n";
        }
        
        // Count users
        $count = $db->query("SELECT COUNT(*) as count FROM tb_user")->fetch(PDO::FETCH_ASSOC);
        echo "\n👥 Total users: " . $count['count'] . "\n";
    } else {
        echo "❌ tb_user table does not exist.\n";
    }
    
    // Check for recent registrations
    echo "\n🕒 Recent registrations (last 5):\n";
    $recentUsers = $db->query("SELECT id_user, email, fullname, date_created FROM tb_user ORDER BY date_created DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    if (count($recentUsers) > 0) {
        foreach ($recentUsers as $user) {
            echo "- {$user['id_user']}: {$user['email']} ({$user['fullname']}) - {$user['date_created']}\n";
        }
    } else {
        echo "No users found in the database.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    
    if ($e->getCode() == '1049') {
        echo "The database 'hobbies' does not exist.\n";
    } elseif ($e->getCode() == '1045') {
        echo "Access denied for user 'root'@'localhost'. Check your database credentials.\n";
    } elseif ($e->getCode() == '2002') {
        echo "Could not connect to MySQL server. Make sure MySQL is running.\n";
    } elseif ($e->getCode() == '42S02') {
        echo "Table not found. The database schema might be incomplete.\n";
    }
}
