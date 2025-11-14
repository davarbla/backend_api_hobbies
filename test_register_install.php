<?php
// Test registration with install ID
$url = 'http://localhost:8000/api/register';
$data = [
    'em' => 'test_install_' . time() . '@test.com',
    'ps' => 'testpassword123',
    'fn' => 'Test Install User',
    'is' => '1', // Install ID - this seems to be required
    'lat' => '48.8575467,2.351375',
    'loc' => 'Paris FR',
    'cc' => 'FR'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

echo "Testing registration with install ID...\n";
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

// Check database
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$email = $data['em'];
$result = $conn->query("SELECT id_user, fullname, email, date_created FROM tb_user WHERE email = '$email' ORDER BY date_created DESC LIMIT 1");

if ($result && $row = $result->fetch_assoc()) {
    echo "\n✅ User successfully created in database:\n";
    echo "ID: {$row['id_user']}, Name: {$row['fullname']}, Email: {$row['email']}, Created: {$row['date_created']}\n";
} else {
    echo "\n❌ User NOT found in database.\n";
}

$conn->close();
?>
