<?php
/**
 * Test the API response to identify JSON issues
 */

echo "=== Testing API Response ===\n\n";

// Simulate API request
$url = 'http://localhost:8000/api/index?lt=0,10';
$data = [
    "lat" => "48.8575467,2.351375",
    "loc" => "Paris FR",
    "cc" => "FR",
    "iu" => "34"
];

$options = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n" .
                    "X-Authentication: ZXJoYWNvcnBkb3Rjb206YjFzbTFsbDRo\r\n",
        'content' => json_encode($data),
        'timeout' => 30
    ]
];

$context = stream_context_create($options);
$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "❌ Failed to connect to API\n";
    echo "Make sure the backend server is running on port 8000\n";
    exit(1);
}

echo "✓ API Response received\n";
echo "Response length: " . strlen($response) . " bytes\n\n";

// Try to decode JSON
$decoded = json_decode($response, true);

if ($decoded === null) {
    echo "❌ JSON PARSING FAILED!\n";
    echo "JSON Error: " . json_last_error_msg() . "\n\n";
    
    // Find the problematic area
    echo "Looking for the error location...\n";
    $errorPos = json_last_error();
    
    // Show a snippet around the error
    if (preg_match('/"country":[^,}]+/', $response, $matches)) {
        echo "\nFound potential issue with country field:\n";
        foreach ($matches as $match) {
            echo "  " . $match . "\n";
        }
    }
    
    // Save full response to file for inspection
    file_put_contents('api_response_debug.json', $response);
    echo "\n✓ Full response saved to: api_response_debug.json\n";
    echo "Check this file to see the exact JSON structure\n";
    
} else {
    echo "✓ JSON parsed successfully!\n";
    echo "\nResponse code: " . ($decoded['code'] ?? 'N/A') . "\n";
    echo "Message: " . ($decoded['message'] ?? 'N/A') . "\n";
    
    if (isset($decoded['result']['latest_post'])) {
        $count = count($decoded['result']['latest_post']);
        echo "Latest posts count: $count\n";
    }
    
    echo "\n✓ API is working correctly!\n";
}
?>
