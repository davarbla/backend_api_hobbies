<?php
// Test the registration endpoint from the Flutter app's IP
$url = 'http://192.168.1.132:8000/api/register';

// Test data matching what the Flutter app sends
$data = [
    'em' => 'test_' . time() . '@example.com',
    'ps' => 'testpass123',
    'is' => 'web_' . time(),
    'lat' => '48.8575467,2.351375',
    'loc' => 'Paris FR',
    'cc' => 'FR',
    'fn' => 'Test User',
    'uf' => 'test_firebase_uid_' . time(),
    'ph' => '',
    'us' => 'testuser' . time()
];

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

// Execute the request
echo "Sending request to: $url\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for errors
if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch) . "\n";
} else {
    echo "HTTP Status: $httpCode\n";
    echo "Response: \n";
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        print_r($decoded);
    } else {
        echo $response;
    }
}

// Close cURL
curl_close($ch);

// Also test localhost to compare
$url = 'http://localhost:8000/api/register';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

echo "\n\nSending same request to: $url\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch) . "\n";
} else {
    echo "HTTP Status: $httpCode\n";
    echo "Response: \n";
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        print_r($decoded);
    } else {
        echo $response;
    }
}

curl_close($ch);
