<?php
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Total users in tb_user:\n";
echo "=====================\n";
$result = $conn->query('SELECT COUNT(*) as count FROM tb_user');
$row = $result->fetch_assoc();
echo 'Count: ' . $row['count'] . PHP_EOL . PHP_EOL;

echo "Latest 5 users:\n";
echo "==============\n";
$result = $conn->query('SELECT id_user, fullname, email, date_created FROM tb_user ORDER BY date_created DESC LIMIT 5');
while ($row = $result->fetch_assoc()) {
    echo 'ID: ' . $row['id_user'] . ', Name: ' . $row['fullname'] . ', Email: ' . $row['email'] . ', Created: ' . $row['date_created'] . PHP_EOL;
}

$conn->close();
?>
