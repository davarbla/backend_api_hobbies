<?php
// Debug what's making the response so large
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

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($httpCode == 200) {
    $decoded = json_decode($response, true);
    
    echo "=== RESPONSE SIZE ANALYSIS ===\n";
    echo "Total response size: " . number_format(strlen($response)) . " bytes\n\n";
    
    if (isset($decoded['result'])) {
        $result = $decoded['result'];
        
        foreach ($result as $key => $value) {
            $size = strlen(json_encode($value));
            $count = is_array($value) ? count($value) : 1;
            echo sprintf("%-20s: %6s bytes, %4d items\n", $key, number_format($size), $count);
            
            // Show sample of large data
            if ($size > 10000 && is_array($value) && count($value) > 0) {
                $firstItem = json_encode($value[0]);
                echo "  First item size: " . strlen($firstItem) . " bytes\n";
                if (strlen($firstItem) > 1000) {
                    echo "  ⚠️  Items are very large!\n";
                }
            }
        }
    }
    
    // Check for large image URLs or base64 data
    if (strpos($response, 'data:image') !== false) {
        echo "\n⚠️  Found base64 image data in response!\n";
    }
    
    $largeFields = [];
    $lines = explode("\n", $response);
    foreach ($lines as $lineNum => $line) {
        if (strlen($line) > 1000) {
            $largeFields[] = "Line $lineNum: " . strlen($line) . " chars";
        }
    }
    
    if (!empty($largeFields)) {
        echo "\nLarge fields found:\n";
        foreach (array_slice($largeFields, 0, 5) as $field) {
            echo "  $field\n";
        }
    }
} else {
    echo "HTTP Error: $httpCode\n";
}
