<?php
// Test that categories are being returned correctly
$url = 'http://localhost:8000/api/index';
$data = [
    'lat' => '48.8575467,2.351375',
    'loc' => 'Paris FR',
    'cc' => 'ZZ',  // International country code
    'iu' => ''
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Authentication: ZXJoYWNvcnBkb3Rjb206YjFzbTFsbDRo'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Testing Categories API\n";
echo "=====================\n";
echo "HTTP Status: $httpCode\n\n";

$responseData = json_decode($response, true);

if ($httpCode == 200 && isset($responseData['result']['category'])) {
    $categories = $responseData['result']['category'];
    echo "✅ SUCCESS: Categories are being returned!\n";
    echo "Total categories returned: " . count($categories) . "\n\n";
    
    echo "Sample categories:\n";
    foreach (array_slice($categories, 0, 5) as $category) {
        echo "- ID: {$category['id_category']}, Title: {$category['title']}, Country: {$category['country']}\n";
    }
    
    echo "\n✅ The Flutter app should now be able to load 'Your Favorite Categories'!\n";
} else {
    echo "❌ ERROR: No categories returned\n";
    print_r($responseData);
}
?>
