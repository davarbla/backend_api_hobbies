<?php
// Test the API endpoint
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

echo "HTTP Status: $httpCode\n";
echo "Response:\n";
$responseData = json_decode($response, true);

if (isset($responseData['result']['category'])) {
    echo "Categories found: " . count($responseData['result']['category']) . "\n";
    foreach ($responseData['result']['category'] as $category) {
        echo "- {$category['id_category']}: {$category['title']} ({$category['country']})\n";
    }
} else {
    echo "No categories in response\n";
    print_r($responseData);
}
?>
