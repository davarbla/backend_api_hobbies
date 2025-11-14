<?php
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Checking for image columns in tb_user:\n";
echo "=====================================\n";

$result = $conn->query('SHOW COLUMNS FROM tb_user LIKE "image%"');
while ($row = $result->fetch_assoc()) {
    echo "- {$row['Field']} - {$row['Type']}\n";
}

echo "\nChecking for gallery-related columns:\n";
echo "======================================\n";

$columns_to_check = ['public', 'friends', 'fun', 'date_img_upd'];
foreach ($columns_to_check as $col) {
    $result = $conn->query("SHOW COLUMNS FROM tb_user LIKE '$col'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "- {$row['Field']} - {$row['Type']}\n";
    } else {
        echo "- $col - MISSING\n";
    }
}

$conn->close();
?>
