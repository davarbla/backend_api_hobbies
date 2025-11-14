<?php
// Test registration endpoint with debugging
$url = 'http://localhost:8000/api/register';
$data = [
    'em' => 'debug_' . time() . '@test.com',
    'ps' => 'testpassword123',
    'fn' => 'Debug User',
    'ph' => '1234567890'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

echo "Testing registration endpoint...\n";
echo "URL: $url\n";
echo "Data: " . json_encode($data) . "\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "HTTP Status: $httpCode\n";
if ($error) {
    echo "CURL Error: $error\n";
}
echo "Response: $response\n";

// Also test if the user was created
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$email = $data['em'];
$result = $conn->query("SELECT id_user, fullname, email, date_created FROM tb_user WHERE email = '$email' ORDER BY date_created DESC LIMIT 1");

if ($result && $row = $result->fetch_assoc()) {
    echo "\nUser found in database:\n";
    echo "ID: {$row['id_user']}, Name: {$row['fullname']}, Email: {$row['email']}, Created: {$row['date_created']}\n";
} else {
    echo "\nUser NOT found in database.\n";
}

$conn->close();
?>
