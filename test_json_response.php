<?php
// Test the API response to check for JSON issues
$testData = [
    'lat' => '48.8575467,2.351375',
    'loc' => 'Paris FR',
    'cc' => 'FR',
    'iu' => '40'
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

echo "Testing /api/index endpoint for JSON validity...\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
} else {
    echo "HTTP Code: $httpCode\n";
    echo "Response length: " . strlen($response) . " bytes\n\n";
    
    // Check if response is valid JSON
    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ JSON Decode Error: " . json_last_error_msg() . "\n";
        echo "Error at position: " . json_last_error() . "\n\n";
        
        // Show first and last parts of response to identify issues
        echo "First 500 chars:\n";
        echo substr($response, 0, 500) . "\n\n";
        
        echo "Last 500 chars:\n";
        echo substr($response, -500) . "\n\n";
        
        // Look for common issues
        if (strpos($response, '<br') !== false) {
            echo "⚠️  Found HTML <br> tags in response - PHP warnings/errors present\n";
        }
        if (strpos($response, 'Warning') !== false || strpos($response, 'Notice') !== false) {
            echo "⚠️  Found PHP warnings/notices in response\n";
        }
    } else {
        echo "✅ JSON is valid!\n";
        if (isset($decoded['result']['latest_post'])) {
            echo "Latest posts count: " . count($decoded['result']['latest_post']) . "\n";
        }
    }
}
