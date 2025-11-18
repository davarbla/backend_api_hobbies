<?php
// Test the /post/latest endpoint
$testData = [
    'lat' => '48.8575467,2.351375',
    'loc' => 'Paris FR',
    'iu' => '40'  // User ID - note: no 'cc' parameter to test the fix
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/post/latest?lt=0,100');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'X-Authentication: Bearer ZXJoYWNvcnBkb3Rjb206YjFzbTFsbDRo'
));

echo "Testing /post/latest endpoint (without cc parameter)...\n";
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
                echo "Latest posts count: " . count($responseData['result'] ?? []) . "\n";
                
                if (!empty($responseData['result'])) {
                    echo "\nFirst 3 posts:\n";
                    foreach (array_slice($responseData['result'], 0, 3) as $post) {
                        echo "  - {$post['title']} (ID: {$post['id_post']})\n";
                    }
                }
            } else {
                echo "\n❌ API error: {$responseData['message']}\n";
            }
        }
    } else {
        echo "\nResponse: " . substr($response, 0, 500) . "\n";
    }
}
