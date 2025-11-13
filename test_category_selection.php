<?php
// Test saving categories for a user
$url = 'http://localhost:8000/api/usercateg';
$data = [
    'id' => '20',
    'ids' => ['171', '160', '119']
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Authentication: ZXJoYWNvcnBkb3Rjb206YjFzbTFsbDRo'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Testing Category Selection\n";
echo "==========================\n";
echo "HTTP Status: $httpCode\n\n";

$responseData = json_decode($response, true);

if ($httpCode == 200 && $responseData['code'] == '200') {
    echo "✅ SUCCESS: Categories saved\n";
    echo "Saved " . count($responseData['result']) . " categories:\n";
    foreach ($responseData['result'] as $cat) {
        echo "- Category ID: {$cat['id_category']}, User ID: {$cat['id_user']}\n";
    }
} else {
    echo "❌ ERROR: " . ($responseData['message'] ?? 'Unknown error') . "\n";
    print_r($responseData);
}

// Now check what categories are in the database
echo "\n\nChecking database:\n";
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

$result = $conn->query("SELECT uc.*, c.title FROM tb_user_category uc JOIN tb_category c ON uc.id_category = c.id_category WHERE uc.id_user = '20' ORDER BY uc.date_created DESC LIMIT 5");
if ($result->num_rows > 0) {
    echo "Categories for user 20:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['title']} (ID: {$row['id_category']})\n";
    }
} else {
    echo "No categories found for user 20\n";
}

$conn->close();
?>
