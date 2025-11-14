<?php
// Database configuration
$db = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'hobbies',
    'port'     => 3306,
];

// Connect to the database
$conn = new mysqli($db['hostname'], $db['username'], $db['password'], $db['database'], $db['port']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Function to update URLs in a table
function updateImageUrls($conn, $table, $fields) {
    foreach ($fields as $field) {
        $sql = "UPDATE $table SET $field = REPLACE($field, 'https://playguys.net/upload/', 'http://localhost:8000/upload/') 
                WHERE $field LIKE '%playguys.net%'";
        
        if ($conn->query($sql) === TRUE) {
            echo "Updated $field in $table: " . $conn->affected_rows . " rows affected\n";
        } else {
            echo "Error updating $field in $table: " . $conn->error . "\n";
        }
    }
}

// Update users table
echo "Updating users table...\n";
updateImageUrls($conn, 'users', ['image', 'image2', 'image3', 'image4', 'image5', 'image6', 'image7']);

// Add similar lines for other tables that might contain image URLs
// For example:
// updateImageUrls($conn, 'posts', ['image_url', 'thumbnail_url']);

echo "Update complete!\n";

$conn->close();
?>
