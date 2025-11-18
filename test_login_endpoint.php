<?php
// Test the login endpoint directly
// Simulate the login request
$testData = [
    'em' => 'davarbla.g@gmail.com',
    'ps' => '123456',
    'ph' => '',
    'is' => '24',
    'lat' => '48.8575467,2.351375',
    'loc' => 'Paris FR',
    'cc' => 'FR',
    'uf' => 'Nv3gWHohSRMeyRZzb7t1bwbKcSk2'
];

// Test with curl to localhost
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'X-Authentication: Bearer ZXJoYWNvcnBkb3Rjb206YjFzbTFsbDRo'
));

echo "Testing login endpoint with Bearer token...\n";
echo "URL: http://localhost:8000/api/login\n";
echo "Data: " . json_encode($testData) . "\n";
echo "Header: X-Authentication: Bearer ZXJoYWNvcnBkb3Rjb206YjFzbTFsbDRo\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
} else {
    echo "HTTP Code: $httpCode\n";
    echo "Response: " . substr($response, 0, 200) . (strlen($response) > 200 ? '...' : '') . "\n";
    
    $responseData = json_decode($response, true);
    if ($responseData && isset($responseData['code'])) {
        if ($responseData['code'] == '200') {
            echo "\n✅ Login successful!\n";
            if (isset($responseData['result'][0])) {
                $user = $responseData['result'][0];
                echo "User ID: {$user['id_user']}\n";
                echo "Email: {$user['email']}\n";
                echo "Name: {$user['fullname']}\n";
            }
        } else {
            echo "\n❌ Login failed: {$responseData['message']}\n";
        }
    }
}
