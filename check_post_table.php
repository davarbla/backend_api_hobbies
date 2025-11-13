<?php
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Checking tb_post table structure:\n";
echo "=================================\n";

$result = $conn->query('DESCRIBE tb_post');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['Field']} - {$row['Type']}\n";
    }
} else {
    echo "Table doesn't exist or error: " . $conn->error . "\n";
}

$conn->close();
?>
