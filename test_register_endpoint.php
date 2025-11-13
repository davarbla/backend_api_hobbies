<?php
// Test data for registration
$data = [
    'em' => 'test_' . time() . '@example.com',
    'ps' => 'testpass123',
    'fn' => 'Test User',
    'is' => 'test_install_' . time(),
    'lat' => '48.8575467,2.351375',
    'loc' => 'Paris FR',
    'cc' => 'FR',
    'uf' => 'test_firebase_uid_' . time(),
    'ph' => '',
    'us' => 'testuser' . time()
];

// Initialize cURL
$ch = curl_init('http://localhost:8000/api/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Output the response
echo "HTTP Status: $httpCode\n";
echo "Response: \n";
$decoded = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    print_r($decoded);
} else {
    echo $response;
}

// Close cURL
curl_close($ch);
