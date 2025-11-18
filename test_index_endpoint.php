<?php
// Test the /api/index endpoint
$testData = [
    'lat' => '48.8575467,2.351375',
    'loc' => 'Paris FR',
    'cc' => 'FR',
    'iu' => '40'  // User ID from login
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/index?lt=0,100');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'X-Authentication: Bearer ZXJoYWNvcnBkb3Rjb206YjFzbTFsbDRo'
));

echo "Testing /api/index endpoint...\n";
echo "URL: http://localhost:8000/api/index?lt=0,100\n";
echo "Data: " . json_encode($testData) . "\n\n";

$start = microtime(true);
$response = curl_exec($ch);
$duration = microtime(true) - $start;
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
} else {
    echo "HTTP Code: $httpCode\n";
    echo "Duration: " . round($duration, 2) . " seconds\n";
    
    if ($httpCode == 200) {
        $responseData = json_decode($response, true);
        if ($responseData && isset($responseData['code'])) {
            if ($responseData['code'] == '200') {
                echo "\n✅ API call successful!\n";
                echo "Categories count: " . count($responseData['result']['category'] ?? []) . "\n";
                echo "Latest posts count: " . count($responseData['result']['latest_post'] ?? []) . "\n";
                echo "My categories count: " . count($responseData['result']['mycategory'] ?? []) . "\n";
                echo "All users count: " . count($responseData['result']['all_user'] ?? []) . "\n";
            } else {
                echo "\n❌ API error: {$responseData['message']}\n";
            }
        }
    } else {
        echo "\nResponse: " . substr($response, 0, 500) . "\n";
    }
}
