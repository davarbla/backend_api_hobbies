<?php
// Test the fixed upload endpoint
$ch = curl_init();

// Create a small test image (1x1 pixel PNG)
$test_image_data = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==';

$data = [
    'filename' => 'test_gallery_image.png',
    'id' => '30',
    'image' => base64_decode($test_image_data), // Send as base64 string like Flutter does
    'imageNumber' => '2',
    'public' => '1',
    'friends' => '0',
    'fun' => '0'
];

$json_data = json_encode($data);

echo "Testing upload endpoint...\n";
echo "URL: http://192.168.1.132:8000/api/upload/upload_image_user\n";
echo "Data: " . substr($json_data, 0, 100) . "...\n\n";

curl_setopt($ch, CURLOPT_URL, "http://192.168.1.132:8000/api/upload/upload_image_user");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json_data)
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status: $http_code\n";
if ($error) {
    echo "CURL Error: $error\n";
}
echo "Response: $response\n";

if ($http_code == 200) {
    $result = json_decode($response, true);
    if ($result && isset($result['code']) && $result['code'] == '200') {
        echo "\n✅ Upload endpoint is working correctly!\n";
        echo "✅ Image should now be saved to tb_user table\n";
    }
} else {
    echo "\n❌ Upload endpoint returned error status: $http_code\n";
}
?>
