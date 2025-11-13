<?php
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Adding missing columns to tb_user_post table...\n";
echo "==============================================\n";

// Add count_interest column
$sql = "ALTER TABLE tb_user_post ADD COLUMN count_interest INT DEFAULT 1 AFTER id_post";
if ($conn->query($sql)) {
    echo "✅ Added 'count_interest' column\n";
} else {
    echo "❌ Failed to add 'count_interest' column: " . $conn->error . "\n";
}

// Add count_like column
$sql = "ALTER TABLE tb_user_post ADD COLUMN count_like INT DEFAULT 0 AFTER count_interest";
if ($conn->query($sql)) {
    echo "✅ Added 'count_like' column\n";
} else {
    echo "❌ Failed to add 'count_like' column: " . $conn->error . "\n";
}

// Add count_post column
$sql = "ALTER TABLE tb_user_post ADD COLUMN count_post INT DEFAULT 0 AFTER count_like";
if ($conn->query($sql)) {
    echo "✅ Added 'count_post' column\n";
} else {
    echo "❌ Failed to add 'count_post' column: " . $conn->error . "\n";
}

// Add count_comment column
$sql = "ALTER TABLE tb_user_post ADD COLUMN count_comment INT DEFAULT 0 AFTER count_post";
if ($conn->query($sql)) {
    echo "✅ Added 'count_comment' column\n";
} else {
    echo "❌ Failed to add 'count_comment' column: " . $conn->error . "\n";
}

echo "\nVerifying table structure:\n";
$result = $conn->query('DESCRIBE tb_user_post');
while ($row = $result->fetch_assoc()) {
    echo "- {$row['Field']} - {$row['Type']}\n";
}

$conn->close();
?>
