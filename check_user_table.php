<?php
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Checking tb_user table structure:\n";
echo "==================================\n";

$result = $conn->query('DESCRIBE tb_user');
while ($row = $result->fetch_assoc()) {
    echo "- {$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}\n";
}

$conn->close();
?>
