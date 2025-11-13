<?php
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Table structure for tb_category:\n";
$result = $conn->query('DESCRIBE tb_category');
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
}

echo "\n\nSample data:\n";
$result = $conn->query('SELECT * FROM tb_category LIMIT 2');
while ($row = $result->fetch_assoc()) {
    print_r($row);
}

$conn->close();
?>
