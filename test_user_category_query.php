<?php
// Test the user category query directly
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Testing user category query...\n";
echo "===============================\n";

$userId = 20;

// Direct query
$sql = "SELECT * FROM tb_user_category WHERE id_user = ? ORDER BY count_interest DESC, date_created ASC LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "✅ Direct query works. Found {$result->num_rows} categories:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- ID: {$row['id_category']}, Interest: {$row['count_interest']}\n";
    }
} else {
    echo "❌ No categories found for user $userId\n";
}

// Now let's see what the actual model is doing
echo "\n\nTesting with joined data (like in CategoryModel):\n";
$sql2 = "SELECT uc.*, c.* 
         FROM tb_user_category uc 
         JOIN tb_category c ON uc.id_category = c.id_category 
         WHERE uc.id_user = ? 
         ORDER BY uc.count_interest DESC, uc.date_created ASC 
         LIMIT 10";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param('i', $userId);
$stmt2->execute();
$result2 = $stmt2->get_result();

if ($result2->num_rows > 0) {
    echo "✅ Joined query works. Found {$result2->num_rows} categories:\n";
    while ($row = $result2->fetch_assoc()) {
        echo "- {$row['title']} (ID: {$row['id_category']})\n";
    }
} else {
    echo "❌ No categories found with joined query\n";
}

$conn->close();
?>
