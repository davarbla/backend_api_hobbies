<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=hobbies;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking users in database...\n";
    
    // Check if table exists
    $tableExists = $db->query("SHOW TABLES LIKE 'tb_user'")->rowCount() > 0;
    
    if (!$tableExists) {
        die("Error: tb_user table does not exist in the database.\n");
    }
    
    // Get users
    $stmt = $db->query('SELECT id_user, email, fullname, status FROM tb_user');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total users: " . count($users) . "\n\n";
    
    if (count($users) > 0) {
        echo "Users in tb_user table:\n";
        foreach ($users as $user) {
            echo "- ID: " . $user['id_user'] . ", ";
            echo "Email: " . $user['email'] . ", ";
            echo "Name: " . $user['fullname'] . ", ";
            echo "Status: " . $user['status'] . "\n";
        }
    } else {
        echo "No users found in tb_user table.\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    if ($e->getCode() == '1049') {
        echo "Error: The database 'hobbies' does not exist.\n";
    } elseif ($e->getCode() == '1045') {
        echo "Error: Access denied for user 'root'@'localhost'. Check your database credentials.\n";
    } elseif ($e->getCode() == '2002') {
        echo "Error: Could not connect to MySQL server. Make sure MySQL is running.\n";
    }
}
