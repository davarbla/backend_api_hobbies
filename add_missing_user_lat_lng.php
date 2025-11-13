<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'hobbies';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to database successfully\n";

// SQL to add missing columns
$sql = "
ALTER TABLE `tb_user` 
ADD COLUMN `lat` varchar(100) DEFAULT NULL,
ADD COLUMN `lng` varchar(100) DEFAULT NULL;

ALTER TABLE `tb_user` ADD INDEX `idx_lat` (`lat`);
ALTER TABLE `tb_user` ADD INDEX `idx_lng` (`lng`);
";

// Execute SQL
if ($conn->multi_query($sql)) {
    echo "Missing lat/lng columns added to tb_user table successfully\n";
    
    // Clear results
    while ($conn->next_result()) {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    }
    
    // Verify columns were added
    $check_sql = "SHOW COLUMNS FROM tb_user LIKE 'lat'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows > 0) {
        echo "✓ 'lat' column verified in tb_user table\n";
    } else {
        echo "✗ 'lat' column not found in tb_user table\n";
    }
    
    $check_sql = "SHOW COLUMNS FROM tb_user LIKE 'lng'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows > 0) {
        echo "✓ 'lng' column verified in tb_user table\n";
    } else {
        echo "✗ 'lng' column not found in tb_user table\n";
    }
    
} else {
    echo "Error adding columns: " . $conn->error . "\n";
}

$conn->close();
?>
