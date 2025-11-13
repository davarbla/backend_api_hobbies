<?php
// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; // Add your MySQL password here if set
$dbName = 'hobbies';

// Path to SQL files
$mainSqlFile = __DIR__ . '/root/mysql/newBD/db_hobbies_v2.sql';
$missingColumnsFile = __DIR__ . '/add_missing_columns.sql';

// Function to execute SQL file
function executeSqlFile($pdo, $file) {
    if (!file_exists($file)) {
        die("Error: File not found: " . $file);
    }
    
    $sql = file_get_contents($file);
    
    try {
        $pdo->exec($sql);
        echo "Successfully executed: " . basename($file) . "\n";
    } catch (PDOException $e) {
        die("Error executing " . basename($file) . ": " . $e->getMessage() . "\n");
    }
}

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$dbName' created or already exists.\n";
    
    // Select the database
    $pdo->exec("USE `$dbName`");
    
    // Import main database file
    executeSqlFile($pdo, $mainSqlFile);
    
    // Import missing columns
    if (file_exists($missingColumnsFile)) {
        executeSqlFile($pdo, $missingColumnsFile);
    } else {
        echo "Note: Missing columns file not found: $missingColumnsFile\n";
    }
    
    echo "Database import completed successfully!\n";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>
