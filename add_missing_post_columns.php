<?php
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Adding missing columns to tb_post table...\n";
echo "==========================================\n";

// Add country column
$sql = "ALTER TABLE tb_post ADD COLUMN country VARCHAR(5) DEFAULT NULL AFTER location";
if ($conn->query($sql)) {
    echo "✅ Added 'country' column\n";
} else {
    echo "❌ Failed to add 'country' column: " . $conn->error . "\n";
}

// Add lat column
$sql = "ALTER TABLE tb_post ADD COLUMN lat VARCHAR(100) DEFAULT NULL AFTER latitude";
if ($conn->query($sql)) {
    echo "✅ Added 'lat' column\n";
} else {
    echo "❌ Failed to add 'lat' column: " . $conn->error . "\n";
}

// Add lng column
$sql = "ALTER TABLE tb_post ADD COLUMN lng VARCHAR(100) DEFAULT NULL AFTER lat";
if ($conn->query($sql)) {
    echo "✅ Added 'lng' column\n";
} else {
    echo "❌ Failed to add 'lng' column: " . $conn->error . "\n";
}

// Add end_date column
$sql = "ALTER TABLE tb_post ADD COLUMN end_date DATETIME DEFAULT NULL AFTER date_updated";
if ($conn->query($sql)) {
    echo "✅ Added 'end_date' column\n";
} else {
    echo "❌ Failed to add 'end_date' column: " . $conn->error . "\n";
}

// Add address column
$sql = "ALTER TABLE tb_post ADD COLUMN address TEXT DEFAULT NULL AFTER location";
if ($conn->query($sql)) {
    echo "✅ Added 'address' column\n";
} else {
    echo "❌ Failed to add 'address' column: " . $conn->error . "\n";
}

// Add address_detail column
$sql = "ALTER TABLE tb_post ADD COLUMN address_detail TEXT DEFAULT NULL AFTER address";
if ($conn->query($sql)) {
    echo "✅ Added 'address_detail' column\n";
} else {
    echo "❌ Failed to add 'address_detail' column: " . $conn->error . "\n";
}

// Add bring column
$sql = "ALTER TABLE tb_post ADD COLUMN bring TEXT DEFAULT NULL AFTER description";
if ($conn->query($sql)) {
    echo "✅ Added 'bring' column\n";
} else {
    echo "❌ Failed to add 'bring' column: " . $conn->error . "\n";
}

// Add cancell column
$sql = "ALTER TABLE tb_post ADD COLUMN cancell TEXT DEFAULT NULL AFTER bring";
if ($conn->query($sql)) {
    echo "✅ Added 'cancell' column\n";
} else {
    echo "❌ Failed to add 'cancell' column: " . $conn->error . "\n";
}

// Add max_people column
$sql = "ALTER TABLE tb_post ADD COLUMN max_people INT DEFAULT NULL AFTER cancell";
if ($conn->query($sql)) {
    echo "✅ Added 'max_people' column\n";
} else {
    echo "❌ Failed to add 'max_people' column: " . $conn->error . "\n";
}

// Add price column
$sql = "ALTER TABLE tb_post ADD COLUMN price DECIMAL(10,2) DEFAULT NULL AFTER max_people";
if ($conn->query($sql)) {
    echo "✅ Added 'price' column\n";
} else {
    echo "❌ Failed to add 'price' column: " . $conn->error . "\n";
}

// Add start_date column
$sql = "ALTER TABLE tb_post ADD COLUMN start_date DATETIME DEFAULT NULL AFTER price";
if ($conn->query($sql)) {
    echo "✅ Added 'start_date' column\n";
} else {
    echo "❌ Failed to add 'start_date' column: " . $conn->error . "\n";
}

// Add age_min column
$sql = "ALTER TABLE tb_post ADD COLUMN age_min INT DEFAULT NULL AFTER end_date";
if ($conn->query($sql)) {
    echo "✅ Added 'age_min' column\n";
} else {
    echo "❌ Failed to add 'age_min' column: " . $conn->error . "\n";
}

// Add age_max column
$sql = "ALTER TABLE tb_post ADD COLUMN age_max INT DEFAULT NULL AFTER age_min";
if ($conn->query($sql)) {
    echo "✅ Added 'age_max' column\n";
} else {
    echo "❌ Failed to add 'age_max' column: " . $conn->error . "\n";
}

// Add fun column
$sql = "ALTER TABLE tb_post ADD COLUMN fun TINYINT(1) DEFAULT NULL AFTER age_max";
if ($conn->query($sql)) {
    echo "✅ Added 'fun' column\n";
} else {
    echo "❌ Failed to add 'fun' column: " . $conn->error . "\n";
}

echo "\nVerifying table structure:\n";
$result = $conn->query('DESCRIBE tb_post');
while ($row = $result->fetch_assoc()) {
    echo "- {$row['Field']} - {$row['Type']}\n";
}

$conn->close();
?>
