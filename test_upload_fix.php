<?php
// Test the upload fix with proper error handling
echo "Testing upload endpoint with null safety fixes...\n\n";

// Create a small test image (1x1 pixel PNG)
$test_image_data = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==';

// Test data that matches what Flutter sends
$data = [
    'filename' => 'test_gallery_image.png',
    'id' => '30',
    'image' => $test_image_data,
    'imageNumber' => '2',
    // Note: NOT sending public, friends, fun - server should determine these
];

$json_data = json_encode($data);

echo "Request data:\n";
echo "- filename: {$data['filename']}\n";
echo "- id: {$data['id']}\n";
echo "- imageNumber: {$data['imageNumber']}\n";
echo "- public, friends, fun: NOT sent (server will determine)\n\n";

$ch = curl_init();

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
echo "Response: $response\n\n";

if ($http_code == 200) {
    $result = json_decode($response, true);
    if ($result && isset($result['code']) && $result['code'] == '200') {
        echo "✅ SUCCESS: Upload endpoint is working!\n";
        echo "✅ Image should be saved to tb_user.image2 (public gallery)\n";
        
        // Verify database update
        $conn = new mysqli('localhost', 'root', '', 'hobbies');
        $result = $conn->query("SELECT image2, public, date_img_upd FROM tb_user WHERE id_user = 30");
        if ($row = $result->fetch_assoc()) {
            echo "✅ Database check:\n";
            echo "  - image2: " . ($row['image2'] ? 'SET' : 'NULL') . "\n";
            echo "  - public: " . $row['public'] . "\n";
            echo "  - date_img_upd: " . ($row['date_img_upd'] ? 'SET' : 'NULL') . "\n";
        }
        $conn->close();
    } else {
        echo "❌ Upload returned error response\n";
    }
} else {
    echo "❌ Upload failed with HTTP status: $http_code\n";
    
    // Check if it's the old "Undefined array key" error
    if (strpos($response, 'Undefined array key') !== false) {
        echo "❌ Still has undefined array key errors\n";
    } else {
        echo "✅ Undefined array key errors are fixed\n";
        echo "❌ But there's still another issue\n";
    }
}
?>
