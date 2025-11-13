<?php
// Test user data
$userData = [
    'em' => 'test_' . time() . '@example.com', // Unique email
    'ps' => 'Test@123',
    'fn' => 'Test User ' . time(),
    'is' => 'test_install_' . time(),
    'ph' => '1234567890',
    'us' => 'testuser' . time(),
    'cc' => 'US'
];

// Convert to JSON
$jsonData = json_encode($userData);

// Initialize cURL
$ch = curl_init('http://localhost:8000/api/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for errors
if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch) . "\n";
} else {
    echo "HTTP Status: $httpCode\n";
    echo "Response: \n";
    print_r(json_decode($response, true));
}

// Close cURL
curl_close($ch);

// Verify the user was added
if ($httpCode === 200) {
    echo "\nVerifying user in database...\n";
    try {
        $db = new PDO('mysql:host=localhost;dbname=hobbies;charset=utf8mb4', 'root', '');
        $stmt = $db->prepare('SELECT * FROM tb_user WHERE email = ?');
        $stmt->execute([$userData['em']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "User found in database:\n";
            print_r($user);
        } else {
            echo "User was not found in the database. Check the registration logs.\n";
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage() . "\n";
    }
}
