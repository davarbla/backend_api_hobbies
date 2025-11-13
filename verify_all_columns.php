<?php
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Verifying all table columns...\n";
echo "===============================\n";

// Check tb_post
echo "\ntb_post table:\n";
echo "---------------\n";
$result = $conn->query('DESCRIBE tb_post');
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
    echo "- {$row['Field']} - {$row['Type']}\n";
}

// Check for important columns
$important = ['country', 'lat', 'lng', 'end_date', 'address', 'address_detail', 'bring', 'cancell', 'max_people', 'price', 'start_date', 'age_min', 'age_max', 'fun'];
echo "\nImportant columns in tb_post:\n";
foreach ($important as $col) {
    if (in_array($col, $columns)) {
        echo "✅ $col exists\n";
    } else {
        echo "❌ $col missing\n";
    }
}

// Check tb_user_post
echo "\n\ntb_user_post table:\n";
echo "--------------------\n";
$result = $conn->query('DESCRIBE tb_user_post');
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
    echo "- {$row['Field']} - {$row['Type']}\n";
}

$important = ['count_interest', 'count_like', 'count_post', 'count_comment'];
echo "\nImportant columns in tb_user_post:\n";
foreach ($important as $col) {
    if (in_array($col, $columns)) {
        echo "✅ $col exists\n";
    } else {
        echo "❌ $col missing\n";
    }
}

// Check tb_user_gallery
echo "\n\ntb_user_gallery table:\n";
echo "------------------------\n";
$result = $conn->query('DESCRIBE tb_user_gallery');
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
    echo "- {$row['Field']} - {$row['Type']}\n";
}

$important = ['id_request', 'count_interest', 'count_like', 'count_post', 'count_comment'];
echo "\nImportant columns in tb_user_gallery:\n";
foreach ($important as $col) {
    if (in_array($col, $columns)) {
        echo "✅ $col exists\n";
    } else {
        echo "❌ $col missing\n";
    }
}

$conn->close();
?>
