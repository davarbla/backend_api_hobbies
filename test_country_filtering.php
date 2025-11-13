<?php
// Test category filtering by country
function testCategories($country, $description) {
    $url = 'http://localhost:8000/api/index';
    $data = [
        'lat' => '48.8575467,2.351375',
        'loc' => 'Test Location',
        'cc' => $country,
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

    $responseData = json_decode($response, true);
    
    echo "\nTesting $description (Country: $country)\n";
    echo str_repeat("-", 50) . "\n";
    
    if ($httpCode == 200 && isset($responseData['result']['category'])) {
        $categories = $responseData['result']['category'];
        echo "✅ SUCCESS: " . count($categories) . " categories returned\n";
        
        // Show country distribution
        $countryCount = [];
        foreach ($categories as $cat) {
            $c = $cat['country'] ?? 'N/A';
            $countryCount[$c] = ($countryCount[$c] ?? 0) + 1;
        }
        
        foreach ($countryCount as $c => $count) {
            echo "  - $c: $count categories\n";
        }
        
        // Show sample
        echo "\nSample categories:\n";
        foreach (array_slice($categories, 0, 3) as $category) {
            echo "  - {$category['title']} ({$category['country']})\n";
        }
    } else {
        echo "❌ ERROR: No categories returned\n";
    }
}

echo "Testing Category Filtering by Country\n";
echo "=====================================\n";

// Test different country codes
testCategories('ZZ', 'International (All Categories)');
testCategories('FR', 'France Categories');
testCategories('ES', 'Spain Categories');
testCategories('US', 'United States Categories');
testCategories('GB', 'UK (should return international + FR/ES/US)');

echo "\n✅ All tests completed!\n";
?>
