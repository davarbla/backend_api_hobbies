<?php
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Adding missing columns to tb_category table...\n";

// List of columns to add
$columns = [
    'id_category_up' => 'int(11) DEFAULT 0',
    'private' => 'smallint(1) DEFAULT 0',
    '`group`' => 'smallint(1) DEFAULT 0',
    'latitude' => 'varchar(255) DEFAULT ""',
    'location' => 'varchar(255) DEFAULT ""',
    'id_owner' => 'int(11) DEFAULT 0',
    'fun' => 'smallint(1) DEFAULT 0',
    'lat' => 'float DEFAULT 0',
    'lng' => 'float DEFAULT 0',
    'country' => 'varchar(2) DEFAULT ""'
];

// Check and add each column if it doesn't exist
foreach ($columns as $column => $definition) {
    // Remove backticks for checking existence
    $columnCheck = str_replace('`', '', $column);
    $result = $conn->query("SHOW COLUMNS FROM tb_category LIKE '$columnCheck'");
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE tb_category ADD COLUMN $column $definition";
        if ($conn->query($sql)) {
            echo "Added column: $columnCheck\n";
        } else {
            echo "Error adding column $columnCheck: " . $conn->error . "\n";
        }
    } else {
        echo "Column $columnCheck already exists\n";
    }
}

echo "\nTable structure after updates:\n";
$result = $conn->query('DESCRIBE tb_category');
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
}

$conn->close();
?>
