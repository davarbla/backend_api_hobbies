<?php
// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; // Add your MySQL password here if set
$dbName = 'hobbies';

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop the database if it exists
    $pdo->exec("DROP DATABASE IF EXISTS `$dbName`");
    echo "Dropped existing database '$dbName'\n";
    
    // Create a new database
    $pdo->exec("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Created new database '$dbName'\n";
    
    // Select the database
    $pdo->exec("USE `$dbName`");
    
    // Import main database file
    $mainSqlFile = __DIR__ . '/root/mysql/newBD/db_hobbies_v2.sql';
    if (file_exists($mainSqlFile)) {
        $sql = file_get_contents($mainSqlFile);
        $pdo->exec($sql);
        echo "Successfully imported main database schema and data\n";
        
        // Import missing columns if the file exists
        $missingColumnsFile = __DIR__ . '/add_missing_columns.sql';
        if (file_exists($missingColumnsFile)) {
            $sql = file_get_contents($missingColumnsFile);
            $pdo->exec($sql);
            echo "Successfully imported missing columns\n";
        } else {
            echo "Note: Missing columns file not found: $missingColumnsFile\n";
        }
        
        echo "Database reset and import completed successfully!\n";
    } else {
        die("Error: Main SQL file not found: $mainSqlFile\n");
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>
