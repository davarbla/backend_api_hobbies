<?php
/**
 * Fix Gallery Columns - Add all missing gallery-related columns to tb_user table
 * This ensures Public Gallery, Friends Gallery, and Fun Gallery work properly
 */

$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "===================================\n";
echo "Fixing Gallery Columns in tb_user\n";
echo "===================================\n\n";

// Get current columns
$columns = [];
$result = $conn->query('DESCRIBE tb_user');
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

echo "Current columns: " . count($columns) . "\n\n";

// Columns we need to add
$columnsToAdd = [
    // Gallery type flags
    ['name' => 'public', 'type' => 'smallint(1)', 'default' => '0', 'null' => 'NOT NULL', 'after' => 'date_updated'],
    ['name' => 'friends', 'type' => 'smallint(1)', 'default' => '0', 'null' => 'NOT NULL', 'after' => 'public'],
    ['name' => 'fun', 'type' => 'smallint(1)', 'default' => '0', 'null' => 'NOT NULL', 'after' => 'friends'],
    ['name' => 'face', 'type' => 'smallint(1)', 'default' => '1', 'null' => 'NOT NULL', 'after' => 'fun'],
    
    // Image update date
    ['name' => 'date_img_upd', 'type' => 'datetime', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'face'],
    
    // Gallery images - Profile: image, Public: 2-4, Friends: 5-7, Fun: 8-10
    ['name' => 'image2', 'type' => 'text', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'date_img_upd'],
    ['name' => 'image3', 'type' => 'text', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'image2'],
    ['name' => 'image4', 'type' => 'text', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'image3'],
    ['name' => 'image5', 'type' => 'text', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'image4'],
    ['name' => 'image6', 'type' => 'text', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'image5'],
    ['name' => 'image7', 'type' => 'text', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'image6'],
    ['name' => 'image8', 'type' => 'text', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'image7'],
    ['name' => 'image9', 'type' => 'text', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'image8'],
    ['name' => 'image10', 'type' => 'text', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'image9'],
    
    // Additional user fields
    ['name' => 'age', 'type' => 'int(11)', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'image10'],
    ['name' => 'height', 'type' => 'int(11)', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'age'],
    ['name' => 'weight', 'type' => 'int(11)', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'height'],
    ['name' => 'position', 'type' => 'int(11)', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'weight'],
    ['name' => 'protection', 'type' => 'int(11)', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'position'],
    ['name' => 'relationship', 'type' => 'int(11)', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'protection'],
    ['name' => 'bodyColor', 'type' => 'int(11)', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'relationship'],
    ['name' => 'bodyShape', 'type' => 'int(11)', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'bodyColor'],
    ['name' => 'hair', 'type' => 'int(11)', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'bodyShape'],
    ['name' => 'publish', 'type' => 'smallint(1)', 'default' => '1', 'null' => 'NOT NULL', 'after' => 'hair'],
    ['name' => 'vip', 'type' => 'smallint(1)', 'default' => '0', 'null' => 'NOT NULL', 'after' => 'publish'],
    ['name' => 'superAdmin', 'type' => 'smallint(1)', 'default' => '0', 'null' => 'NOT NULL', 'after' => 'vip'],
    ['name' => 'lat', 'type' => 'varchar(100)', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'superAdmin'],
    ['name' => 'lng', 'type' => 'varchar(100)', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'lat'],
    ['name' => 'message', 'type' => 'text', 'default' => 'NULL', 'null' => 'DEFAULT NULL', 'after' => 'lng'],
    ['name' => 'reliable', 'type' => 'int(11)', 'default' => '100', 'null' => 'NOT NULL', 'after' => 'message'],
    ['name' => 'sexy', 'type' => 'int(11)', 'default' => '0', 'null' => 'NOT NULL', 'after' => 'reliable'],
    ['name' => 'ugly', 'type' => 'smallint(1)', 'default' => '0', 'null' => 'NOT NULL', 'after' => 'sexy'],
];

$added = 0;
$skipped = 0;
$failed = 0;

foreach ($columnsToAdd as $col) {
    if (in_array($col['name'], $columns)) {
        echo "⏭️  Column '{$col['name']}' already exists - skipped\n";
        $skipped++;
        continue;
    }
    
    $defaultClause = '';
    if ($col['default'] !== 'NULL') {
        $defaultClause = " DEFAULT '{$col['default']}'";
    } else if ($col['null'] === 'DEFAULT NULL') {
        $defaultClause = " DEFAULT NULL";
    }
    
    $sql = "ALTER TABLE tb_user ADD COLUMN `{$col['name']}` {$col['type']} {$col['null']}{$defaultClause} AFTER `{$col['after']}`";
    
    if ($conn->query($sql)) {
        echo "✅ Added '{$col['name']}' column\n";
        $added++;
    } else {
        echo "❌ Failed to add '{$col['name']}' column: " . $conn->error . "\n";
        $failed++;
    }
}

echo "\n";
echo "Summary:\n";
echo "--------\n";
echo "✅ Added: $added\n";
echo "⏭️  Skipped: $skipped\n";
echo "❌ Failed: $failed\n";
echo "\n";

// Verify the gallery columns
echo "Verifying gallery columns:\n";
echo "==========================\n";
$galleryColumns = ['image2', 'image3', 'image4', 'image5', 'image6', 'image7', 'image8', 'image9', 'image10', 'public', 'friends', 'fun', 'face'];
$result = $conn->query('DESCRIBE tb_user');
$found = [];
while ($row = $result->fetch_assoc()) {
    if (in_array($row['Field'], $galleryColumns)) {
        $found[] = $row['Field'];
        echo "✓ {$row['Field']} - {$row['Type']}\n";
    }
}

$missing = array_diff($galleryColumns, $found);
if (!empty($missing)) {
    echo "\n⚠️  Still missing: " . implode(', ', $missing) . "\n";
} else {
    echo "\n✅ All gallery columns present!\n";
}

$conn->close();
?>
