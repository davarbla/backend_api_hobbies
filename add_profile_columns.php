<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=hobbies', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Adding missing columns to tb_user table...\n";
    
    // Add missing columns one by one to avoid errors if they already exist
    $columns = [
        'height' => "VARCHAR(10) DEFAULT '0'",
        'weight' => "VARCHAR(10) DEFAULT '0'", 
        'position' => "VARCHAR(50) DEFAULT '0'",
        'protection' => "VARCHAR(20) DEFAULT '0'",
        'relationship' => "VARCHAR(20) DEFAULT '0'",
        'bodyColor' => "VARCHAR(20) DEFAULT '0'",
        'bodyShape' => "VARCHAR(20) DEFAULT '0'",
        'hair' => "VARCHAR(20) DEFAULT '0'",
        'publish' => "VARCHAR(10) DEFAULT '1'"
    ];
    
    foreach ($columns as $column => $definition) {
        try {
            $sql = "ALTER TABLE tb_user ADD COLUMN $column $definition";
            $pdo->exec($sql);
            echo "✅ Added column: $column\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "⚠️  Column $column already exists\n";
            } else {
                echo "❌ Error adding $column: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Verify all columns
    echo "\n📋 Current tb_user columns:\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM tb_user");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    echo "\n✅ Database update completed!\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection error: " . $e->getMessage() . "\n";
}
?>
