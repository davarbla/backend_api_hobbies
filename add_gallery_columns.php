<?php
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "Adding missing gallery columns to tb_user table...\n";
echo "==================================================\n";

// Add public column
$sql = "ALTER TABLE tb_user ADD COLUMN `public` smallint(1) NOT NULL DEFAULT '1' AFTER `date_updated`";
if ($conn->query($sql)) {
    echo "✅ Added 'public' column\n";
} else {
    echo "❌ Failed to add 'public' column: " . $conn->error . "\n";
}

// Add friends column
$sql = "ALTER TABLE tb_user ADD COLUMN `friends` smallint(1) NOT NULL DEFAULT '0' AFTER `public`";
if ($conn->query($sql)) {
    echo "✅ Added 'friends' column\n";
} else {
    echo "❌ Failed to add 'friends' column: " . $conn->error . "\n";
}

// Add fun column
$sql = "ALTER TABLE tb_user ADD COLUMN `fun` smallint(1) NOT NULL DEFAULT '0' AFTER `friends`";
if ($conn->query($sql)) {
    echo "✅ Added 'fun' column\n";
} else {
    echo "❌ Failed to add 'fun' column: " . $conn->error . "\n";
}

// Add date_img_upd column
$sql = "ALTER TABLE tb_user ADD COLUMN `date_img_upd` datetime DEFAULT NULL AFTER `fun`";
if ($conn->query($sql)) {
    echo "✅ Added 'date_img_upd' column\n";
} else {
    echo "❌ Failed to add 'date_img_upd' column: " . $conn->error . "\n";
}

// Add additional image columns for multiple gallery images
$image_columns = ['image2', 'image3', 'image4', 'image5', 'image6', 'image7'];
foreach ($image_columns as $col) {
    $sql = "ALTER TABLE tb_user ADD COLUMN `$col` text DEFAULT NULL AFTER `date_img_upd`";
    if ($conn->query($sql)) {
        echo "✅ Added '$col' column\n";
    } else {
        echo "❌ Failed to add '$col' column: " . $conn->error . "\n";
    }
}

// Add ugly column if missing
$sql = "ALTER TABLE tb_user ADD COLUMN `ugly` smallint(1) NOT NULL DEFAULT '0' AFTER `image7`";
if ($conn->query($sql)) {
    echo "✅ Added 'ugly' column\n";
} else {
    echo "❌ Failed to add 'ugly' column: " . $conn->error . "\n";
}

// Add age column if missing
$sql = "ALTER TABLE tb_user ADD COLUMN `age` int(11) DEFAULT NULL AFTER `ugly`";
if ($conn->query($sql)) {
    echo "✅ Added 'age' column\n";
} else {
    echo "❌ Failed to add 'age' column: " . $conn->error . "\n";
}

// Add message column if missing
$sql = "ALTER TABLE tb_user ADD COLUMN `message` text DEFAULT NULL AFTER `age`";
if ($conn->query($sql)) {
    echo "✅ Added 'message' column\n";
} else {
    echo "❌ Failed to add 'message' column: " . $conn->error . "\n";
}

echo "\nVerifying updated table structure:\n";
echo "==================================\n";
$result = $conn->query('DESCRIBE tb_user');
while ($row = $result->fetch_assoc()) {
    echo "- {$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}\n";
}

$conn->close();
?>
