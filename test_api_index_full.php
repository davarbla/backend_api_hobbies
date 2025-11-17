<?php
// Test the actual API index endpoint

$url = 'http://localhost:8000/api/index?lt=0,10000';
$postData = json_encode([
    'lat' => '48.8711983,2.3857083',
    'loc' => 'Paris FR',
    'cc' => 'FR',
    'iu' => '42'
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($postData)
]);

echo "Making request to: $url\n";
echo "Post data: $postData\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response size: " . strlen($response) . " bytes\n\n";

if ($httpCode != 200) {
    echo "Error response:\n";
    echo substr($response, 0, 2000) . "\n";
    exit(1);
}

// Try to decode JSON
$decoded = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON decode error: " . json_last_error_msg() . "\n";
    echo "Error at position: " . json_last_error() . "\n\n";
    
    // Find the problem area
    $errorPos = 156803; // From the Flutter error
    if (strlen($response) > $errorPos) {
        echo "Context around error position $errorPos:\n";
        $start = max(0, $errorPos - 200);
        $end = min(strlen($response), $errorPos + 200);
        echo substr($response, $start, $end - $start) . "\n\n";
    }
    
    // Save full response to file for inspection
    file_put_contents('api_response_debug.json', $response);
    echo "Full response saved to api_response_debug.json\n";
} else {
    echo "JSON decode successful!\n";
    echo "Result code: " . ($decoded['code'] ?? 'N/A') . "\n";
    echo "Message: " . ($decoded['message'] ?? 'N/A') . "\n";
    
    if (isset($decoded['result'])) {
        foreach ($decoded['result'] as $key => $value) {
            if (is_array($value)) {
                echo "  $key: " . count($value) . " items\n";
            }
        }
    }
}
