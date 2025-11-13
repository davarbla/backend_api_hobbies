<?php
// Test the API with a logged-in user
$url = 'http://localhost:8000/api/index';
$data = [
    'lat' => '48.8575467,2.351375',
    'loc' => 'Paris FR',
    'cc' => 'FR',
    'iu' => '20'  // User ID
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

echo "Testing API with User ID\n";
echo "========================\n";
echo "HTTP Status: $httpCode\n\n";

$responseData = json_decode($response, true);

if ($httpCode == 200 && isset($responseData['result'])) {
    echo "✅ SUCCESS: API response received\n\n";
    
    // Check main categories
    if (isset($responseData['result']['category'])) {
        echo "Main Categories: " . count($responseData['result']['category']) . " items\n";
    }
    
    // Check user categories (My Communities)
    if (isset($responseData['result']['mycategory'])) {
        echo "My Categories (Communities): " . count($responseData['result']['mycategory']) . " items\n";
        if (!empty($responseData['result']['mycategory'])) {
            echo "  Sample:\n";
            foreach (array_slice($responseData['result']['mycategory'], 0, 3) as $cat) {
                echo "  - {$cat['title']} (ID: {$cat['id_category']})\n";
            }
        }
    }
} else {
    echo "❌ ERROR: " . ($responseData['message'] ?? 'Unknown error') . "\n";
}
?>
