<?php
// Test that image URLs are using the local server
$url = 'http://localhost:8000/api/index';
$data = [
    'lat' => '48.8575467,2.351375',
    'loc' => 'Paris FR',
    'cc' => 'ZZ',
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

echo "Testing Image URLs\n";
echo "==================\n";
echo "HTTP Status: $httpCode\n\n";

$responseData = json_decode($response, true);

if ($httpCode == 200 && isset($responseData['result']['category'])) {
    $categories = $responseData['result']['category'];
    
    echo "Checking image URLs:\n";
    $localUrls = 0;
    $prodUrls = 0;
    
    foreach ($categories as $category) {
        $imageUrl = $category['image'] ?? '';
        if (strpos($imageUrl, '192.168.1.132:8000') !== false) {
            $localUrls++;
        } elseif (strpos($imageUrl, 'hobbies.fboys.app') !== false) {
            $prodUrls++;
            echo "❌ Found production URL: {$category['title']} - $imageUrl\n";
        }
    }
    
    echo "\n✅ Local URLs: $localUrls\n";
    if ($prodUrls > 0) {
        echo "❌ Production URLs: $prodUrls\n";
    } else {
        echo "✅ No production URLs found\n";
    }
    
    echo "\nSample image URLs:\n";
    foreach (array_slice($categories, 0, 3) as $category) {
        echo "- {$category['title']}: " . substr($category['image'], 0, 60) . "...\n";
    }
} else {
    echo "❌ ERROR: No categories returned\n";
}
?>
