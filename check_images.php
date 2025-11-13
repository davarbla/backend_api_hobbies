<?php
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Checking image URLs in categories:\n";
echo "===================================\n";

$result = $conn->query("SELECT id_category, title, image, country FROM tb_category LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id_category']}\n";
    echo "Title: {$row['title']}\n";
    echo "Country: {$row['country']}\n";
    echo "Image: " . substr($row['image'], 0, 80) . "...\n";
    echo str_repeat("-", 50) . "\n";
}

$conn->close();
?>
