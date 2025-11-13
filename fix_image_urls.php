<?php
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Updating image URLs from production to local...\n";
echo "==============================================\n";

// Update all image URLs from production to local
$sql = "UPDATE tb_category 
        SET image = REPLACE(image, 'https://hobbies.fboys.app', 'http://192.168.1.132:8000')
        WHERE image LIKE 'https://hobbies.fboys.app%'";

if ($conn->query($sql)) {
    $rowsAffected = $conn->affected_rows;
    echo "Updated $rowsAffected image URLs\n\n";
    
    // Verify the update
    $result = $conn->query("SELECT id_category, title, image FROM tb_category LIMIT 3");
    echo "Sample updated URLs:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['title']}: " . substr($row['image'], 0, 80) . "...\n";
    }
} else {
    echo "Error updating URLs: " . $conn->error . "\n";
}

$conn->close();
?>
