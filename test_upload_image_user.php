<?php
// Test the upload_image_user endpoint
$url = 'http://192.168.1.132:8000/api/upload/upload_image_user';

// Sample data for testing
$data = [
    'filename' => 'test_image.jpg',
    'id' => '30',
    'image' => base64_encode(file_get_contents('test_image.jpg')), // You'll need to create a test image
    'imageNumber' => '2',
    'public' => '1',
    'friends' => '0',
    'fun' => '0'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer YOUR_AUTH_TOKEN' // You may need to add proper auth
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
?>
