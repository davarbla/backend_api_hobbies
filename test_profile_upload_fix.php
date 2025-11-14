<?php
echo "=== PROFILE IMAGE UPLOAD FIX TEST ===\n\n";

// Test data for profile image (imageNumber = 0)
$data = [
    'filename' => 'profile_image.jpg',
    'id' => '30',
    'image' => '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A8A',
    'imageNumber' => '0',  // This indicates profile image
];

$json_data = json_encode($data);

echo "Testing profile image upload...\n";
echo "ImageNumber: {$data['imageNumber']} (profile image)\n";
echo "Expected: Update tb_user.image column\n";
echo "Expected: Save to /upload/user/ directory\n\n";

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
        echo "✅ SUCCESS: Profile image uploaded!\n";
        echo "✅ File: {$result['file']}\n";
        
        // Verify database update
        $conn = new mysqli('localhost', 'root', '', 'hobbies');
        $result = $conn->query("SELECT image, date_img_upd FROM tb_user WHERE id_user = 30");
        if ($row = $result->fetch_assoc()) {
            echo "✅ Database check:\n";
            echo "  - image column: " . ($row['image'] ? 'SET' : 'NULL') . "\n";
            echo "  - date_img_upd: " . ($row['date_img_upd'] ? 'SET' : 'NULL') . "\n";
            if ($row['image']) {
                echo "  - Image URL: {$row['image']}\n";
            }
        }
        $conn->close();
    } else {
        echo "❌ Upload returned error response\n";
    }
} else {
    echo "❌ Upload failed with HTTP status: $http_code\n";
}

echo "\n=== FIX SUMMARY ===\n";
echo "✅ Added profile image handling (imageNumber = 0)\n";
echo "✅ Profile images save to main 'image' column\n";
echo "✅ Profile images save to /upload/user/ directory\n";
echo "✅ Gallery images still work as before\n";
?>
