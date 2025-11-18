<?php
// Test with smaller limit
$testData = [
    'lat' => '48.8575467,2.351375',
    'loc' => 'Paris FR',
    'cc' => 'FR',
    'iu' => '40'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/index?lt=0,10');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'X-Authentication: Bearer ZXJoYWNvcnBkb3Rjb206YjFzbTFsbDRo'
));

echo "Testing /api/index with limit 10...\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($httpCode == 200) {
    echo "HTTP Code: $httpCode\n";
    echo "Response size: " . number_format(strlen($response)) . " bytes\n\n";
    
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ JSON is valid!\n";
        if (isset($decoded['result']['latest_post'])) {
            echo "Latest posts count: " . count($decoded['result']['latest_post']) . "\n";
        }
        if (isset($decoded['result']['category'])) {
            echo "Categories count: " . count($decoded['result']['category']) . "\n";
        }
    } else {
        echo "❌ JSON Error: " . json_last_error_msg() . "\n";
    }
} else {
    echo "HTTP Error: $httpCode\n";
}
