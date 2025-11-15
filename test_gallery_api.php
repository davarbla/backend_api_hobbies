<?php
/**
 * Test Gallery API - Verify user gallery data is returned correctly
 */

$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "Testing Gallery API Response\n";
echo "============================\n\n";

// Get a sample user to test
$result = $conn->query("SELECT * FROM tb_user WHERE status = 1 LIMIT 1");
$user = $result->fetch_object();

if (!$user) {
    echo "❌ No active users found in database\n";
    exit;
}

echo "Testing with user: {$user->fullname} (ID: {$user->id_user})\n";
echo "-------------------------------------------\n\n";

// Check gallery columns
$galleryColumns = [
    'image' => 'Profile',
    'image2' => 'Public Gallery 1',
    'image3' => 'Public Gallery 2',
    'image4' => 'Public Gallery 3',
    'image5' => 'Friends Gallery 1',
    'image6' => 'Friends Gallery 2',
    'image7' => 'Friends Gallery 3',
    'image8' => 'Fun Gallery 1',
    'image9' => 'Fun Gallery 2',
    'image10' => 'Fun Gallery 3'
];

echo "Gallery Images:\n";
echo "---------------\n";
foreach ($galleryColumns as $col => $label) {
    $value = isset($user->$col) ? $user->$col : 'NULL';
    $status = (!empty($value) && $value != 'NULL') ? '✓' : '✗';
    echo "$status $label ($col): " . (strlen($value ?? '') > 50 ? substr($value, 0, 50) . '...' : ($value ?? 'NULL')) . "\n";
}

echo "\nGallery Flags:\n";
echo "--------------\n";
echo "Public Gallery: " . (isset($user->public) ? ($user->public ? '✓ Enabled' : '✗ Disabled') : '❌ Column missing') . "\n";
echo "Friends Gallery: " . (isset($user->friends) ? ($user->friends ? '✓ Enabled' : '✗ Disabled') : '❌ Column missing') . "\n";
echo "Fun Gallery: " . (isset($user->fun) ? ($user->fun ? '✓ Enabled' : '✗ Disabled') : '❌ Column missing') . "\n";
echo "Face Visible: " . (isset($user->face) ? ($user->face ? '✓ Yes' : '✗ No') : '❌ Column missing') . "\n";

// Test the API response format
echo "\n\nTesting API JSON Response:\n";
echo "==========================\n";

$userArray = (array) $user;
$apiResponse = [
    'id_user' => $userArray['id_user'] ?? null,
    'fullname' => $userArray['fullname'] ?? null,
    'image' => $userArray['image'] ?? null,
    'image2' => $userArray['image2'] ?? null,
    'image3' => $userArray['image3'] ?? null,
    'image4' => $userArray['image4'] ?? null,
    'image5' => $userArray['image5'] ?? null,
    'image6' => $userArray['image6'] ?? null,
    'image7' => $userArray['image7'] ?? null,
    'image8' => $userArray['image8'] ?? null,
    'image9' => $userArray['image9'] ?? null,
    'image10' => $userArray['image10'] ?? null,
    'public' => $userArray['public'] ?? 0,
    'friends' => $userArray['friends'] ?? 0,
    'fun' => $userArray['fun'] ?? 0,
    'face' => $userArray['face'] ?? 1
];

echo json_encode($apiResponse, JSON_PRETTY_PRINT);

// Show recommendations
echo "\n\nRecommendations:\n";
echo "================\n";

$issues = 0;

if (!isset($user->public) || !isset($user->friends) || !isset($user->fun)) {
    echo "❌ Gallery flag columns are missing - run fix_gallery_columns.php\n";
    $issues++;
}

if (empty($user->image2) && empty($user->image3) && empty($user->image4)) {
    echo "⚠️  No images in Public Gallery (image2-4)\n";
    $issues++;
}

if (empty($user->image5) && empty($user->image6) && empty($user->image7)) {
    echo "⚠️  No images in Friends Gallery (image5-7)\n";
    $issues++;
}

if (empty($user->image8) && empty($user->image9) && empty($user->image10)) {
    echo "⚠️  No images in Fun Gallery (image8-10)\n";
    $issues++;
}

if ($issues == 0) {
    echo "✅ Everything looks good!\n";
} else {
    echo "\nTo test uploads:\n";
    echo "1. Open the gallery page in the Flutter app\n";
    echo "2. Try uploading images to each gallery section\n";
    echo "3. Check that the API updates the correct image columns\n";
}

?>
