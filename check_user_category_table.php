<?php
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Checking tb_user_category table structure:\n";
echo "==========================================\n";

$result = $conn->query('DESCRIBE tb_user_category');
while ($row = $result->fetch_assoc()) {
    echo "- {$row['Field']} - {$row['Type']}\n";
}

$conn->close();
?>
