<?php
/**
 * Test the push_fcm endpoint directly to see if it works
 */

// Simulate the exact request that Flutter is sending
$testToken = "dGwRfLy6RN-T78CClL2lTQ:APA91bFein2QvxwFUK8iNerVp6CUHdtOD8KPb8hbQNJh-C4YB-nWEAD4DZ_PyW1Q9uCxJbSOfiwFPGaZouTkog_ehLfyh2R86GCDAsJbXLnrgJksGmcTNX0";

$testData = array(
    'title' => 'Test Notification',
    'body' => 'This is a test message',
    'image' => '',
    'payload' => array(
        'keyname' => 'test',
        'message' => 'Test notification'
    )
);

$requestBody = array(
    'token' => $testToken,
    'data' => $testData
);

echo "=== TESTING PUSH_FCM ENDPOINT ===\n\n";
echo "Request Data:\n";
print_r($requestBody);
echo "\n";

// Convert to JSON
$jsonBody = json_encode($requestBody);
echo "JSON Body Length: " . strlen($jsonBody) . " bytes\n";
echo "JSON Valid: " . (json_last_error() === JSON_ERROR_NONE ? "YES" : "NO") . "\n\n";

// Make the API call
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:8000/user/push_fcm");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'X-Authentication: ZXJoYWNvcnBkb3Rjb206YjFzbTFsbDRo' // Your auth token
));

echo "Sending request to: http://localhost:8000/user/push_fcm\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";

if ($error) {
    echo "CURL Error: $error\n\n";
} else {
    echo "Response:\n";
    echo $response . "\n\n";
    
    // Try to decode response
    $decoded = json_decode($response, true);
    if ($decoded) {
        echo "Decoded Response:\n";
        print_r($decoded);
    }
}

echo "\n=== END OF TEST ===\n";
