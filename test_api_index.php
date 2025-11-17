<?php
// Test the API index endpoint to identify JSON encoding issues

// Set higher memory limit for large JSON
ini_set('memory_limit', '512M');

// Database connection
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'hobbies';

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for fields that might have problematic characters
$tables = ['tb_user', 'tb_post', 'tb_category', 'tb_comment'];

foreach ($tables as $table) {
    echo "Checking $table...\n";
    
    // Get column names
    $result = $conn->query("SHOW COLUMNS FROM $table");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Check for problematic characters in text fields
    foreach ($columns as $column) {
        $sql = "SELECT * FROM `$table` WHERE `$column` REGEXP '[^[:print:][:space:]]' LIMIT 5";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo "  Found non-printable characters in $table.$column:\n";
            while ($row = $result->fetch_assoc()) {
                $value = $row[$column];
                $hex = bin2hex($value);
                echo "    Row ID: " . (isset($row['id_user']) ? $row['id_user'] : (isset($row['id_post']) ? $row['id_post'] : $row[array_key_first($row)])) . "\n";
                echo "    Value: " . substr($value, 0, 50) . "...\n";
                echo "    Hex: " . substr($hex, 0, 100) . "...\n\n";
            }
        }
    }
}

// Test JSON encoding of all users
echo "\n\nTesting JSON encode of all users...\n";
$result = $conn->query("SELECT * FROM tb_user LIMIT 100");
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$json = json_encode($users);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON encoding error: " . json_last_error_msg() . "\n";
    
    // Find which user causes the issue
    foreach ($users as $idx => $user) {
        $testJson = json_encode($user);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Problem with user ID: {$user['id_user']}\n";
            echo "Error: " . json_last_error_msg() . "\n";
            print_r($user);
        }
    }
} else {
    echo "JSON encoding successful! Size: " . strlen($json) . " bytes\n";
}

$conn->close();
