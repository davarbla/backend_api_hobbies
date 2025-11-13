<?php
// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; // Add your MySQL password here if set
$dbName = 'hobbies';

try {
    // Create connection
    $conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Successfully connected to the database.\n";
    
    // Check if tb_user exists
    $stmt = $conn->query("SHOW TABLES LIKE 'tb_user'");
    if ($stmt->rowCount() > 0) {
        echo "✅ tb_user table exists.\n";
        
        // Check table structure
        echo "\n📋 tb_user table structure:\n";
        $columns = $conn->query("SHOW COLUMNS FROM tb_user")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "- {$column['Field']} | {$column['Type']} | {$column['Null']} | {$column['Key']} | {$column['Default']}\n";
        }
        
        // Test INSERT permission
        try {
            $testEmail = 'test_permission_' . time() . '@example.com';
            $stmt = $conn->prepare("INSERT INTO tb_user (email, password_user, fullname, username, id_install, uid_fcm, latitude, location, country, date_created, date_updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([
                $testEmail,
                'testpass',
                'Test User',
                'testuser' . time(),
                'test_install_' . time(),
                'test_firebase_uid_' . time(),
                '0,0',
                'Test Location',
                'US'
            ]);
            $lastId = $conn->lastInsertId();
            echo "\n✅ Successfully inserted test user with ID: $lastId\n";
            
            // Clean up
            $conn->exec("DELETE FROM tb_user WHERE email = '" . $testEmail . "'");
            echo "✅ Cleaned up test user.\n";
        } catch (PDOException $e) {
            echo "\n❌ Error inserting test user: " . $e->getMessage() . "\n";
            echo "SQL Error Code: " . $e->errorInfo[1] . "\n";
            echo "SQL State: " . $e->errorInfo[0] . "\n";
        }
    } else {
        echo "❌ tb_user table does not exist.\n";
    }
    
    // Check for recent registrations
    echo "\n👥 Recent registrations (last 5):\n";
    $recentUsers = $conn->query("SELECT id_user, email, fullname, date_created FROM tb_user ORDER BY date_created DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    if (count($recentUsers) > 0) {
        foreach ($recentUsers as $user) {
            echo "- {$user['id_user']}: {$user['email']} ({$user['fullname']}) - {$user['date_created']}\n";
        }
    } else {
        echo "No users found in the database.\n";
    }
    
} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage() . "\n");
}
