<?php
// Test script to verify the upload endpoint works
$ch = curl_init();

// Create a small test image
$test_image_data = base64_encode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==');

$data = [
    'filename' => 'test.png',
    'id' => '30',
    'image' => $test_image_data,
    'imageNumber' => '2',
    'public' => '1',
    'friends' => '0',
    'fun' => '0'
];

curl_setopt($ch, CURLOPT_URL, "http://192.168.1.132:8000/api/upload/upload_image_user");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
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
?>
