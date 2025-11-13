<?php
// Database configuration
$host = 'localhost';
$dbname = 'hobbies';
$username = 'root';
$password = '';

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Successfully connected to the database.\n\n";
    
    // Check if users table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'tb_user'")->rowCount() > 0;
    
    if ($tableExists) {
        echo "Table 'tb_user' exists.\n";
        
        // Count users
        $count = $pdo->query("SELECT COUNT(*) as count FROM tb_user")->fetch(PDO::FETCH_ASSOC)['count'];
        echo "Total users: $count\n\n";
        
        // Show first 5 users
        $users = $pdo->query("SELECT id_user, email, username, fullname, status FROM tb_user LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        echo "Sample users:\n";
        print_r($users);
        
        // Show table structure
        echo "\nTable structure for tb_user:\n";
        $columns = $pdo->query("DESCRIBE tb_user")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']}) {$column['Null']} {$column['Key']} {$column['Default']}\n";
        }
    } else {
        echo "Table 'tb_user' does not exist.\n";
        
        // Show all tables
        echo "\nAvailable tables in database:\n";
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        print_r($tables);
    }
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}
?>
