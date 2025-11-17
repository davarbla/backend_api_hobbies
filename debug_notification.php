<?php
/**
 * Debug script to test notification sending for event join
 * Run this to diagnose why notifications aren't being received
 */

// Include CodeIgniter bootstrap
require_once __DIR__ . '/root/app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';

use App\Models\UserModel;
use App\Models\PostModel;
use App\Models\InstallModel;
use App\Models\CategoryModel;

echo "=== NOTIFICATION DEBUG SCRIPT ===\n\n";

// Initialize models
$userModel = new UserModel();
$postModel = new PostModel();
$installModel = new InstallModel();
$categoryModel = new CategoryModel();

// Test parameters
$eventId = 3; // Event ID
$ownerEmail = 'davarbla.g@gmail.com'; // Event owner email

echo "1. Checking Event ID: $eventId\n";
echo str_repeat("-", 50) . "\n";

// Get event details
$event = $postModel->where('id_post', $eventId)->first();
if (!$event) {
    die("❌ ERROR: Event ID $eventId not found!\n");
}

echo "✅ Event found:\n";
echo "   - Title: " . ($event['title'] ?? 'N/A') . "\n";
echo "   - Owner User ID: " . $event['id_user'] . "\n";
echo "   - Category ID: " . $event['id_category'] . "\n";
echo "   - Description: " . substr($event['description'], 0, 50) . "...\n\n";

// Get event owner
echo "2. Checking Event Owner\n";
echo str_repeat("-", 50) . "\n";

$owner = $userModel->where('id_user', $event['id_user'])->first();
if (!$owner) {
    die("❌ ERROR: Event owner not found!\n");
}

echo "✅ Owner found:\n";
echo "   - Name: " . $owner['fullname'] . "\n";
echo "   - Email: " . $owner['email'] . "\n";
echo "   - User ID: " . $owner['id_user'] . "\n";
echo "   - Install ID: " . ($owner['id_install'] ?? 'NULL') . "\n\n";

// Check if this is the expected owner
if ($owner['email'] !== $ownerEmail) {
    echo "⚠️  WARNING: Event owner email doesn't match!\n";
    echo "   Expected: $ownerEmail\n";
    echo "   Found: " . $owner['email'] . "\n\n";
}

// Get FCM token
echo "3. Checking FCM Token\n";
echo str_repeat("-", 50) . "\n";

if (empty($owner['id_install'])) {
    die("❌ ERROR: Owner has no id_install (not properly registered)!\n");
}

$install = $installModel->where('id_install', $owner['id_install'])->first();
if (!$install) {
    die("❌ ERROR: Install record not found for id_install: " . $owner['id_install'] . "\n");
}

echo "✅ Install record found:\n";
echo "   - Install ID: " . $install['id_install'] . "\n";
echo "   - UUID: " . ($install['uuid'] ?? 'NULL') . "\n";
echo "   - OS Platform: " . ($install['os_platform'] ?? 'NULL') . "\n";
echo "   - Token FCM: " . ($install['token_fcm'] ?? 'NULL') . "\n";

if (empty($install['token_fcm'])) {
    echo "\n❌ CRITICAL ERROR: FCM Token is NULL or EMPTY!\n";
    echo "   This is why notifications are not being received.\n";
    echo "   The user needs to:\n";
    echo "   1. Rebuild the Flutter app with the token fix\n";
    echo "   2. Uninstall and reinstall the app\n";
    echo "   3. Launch the app and login\n";
    echo "   4. Check app logs for 'FCM Token saved successfully'\n\n";
    die();
} else {
    echo "\n✅ FCM Token exists! Length: " . strlen($install['token_fcm']) . " chars\n";
    echo "   Token preview: " . substr($install['token_fcm'], 0, 30) . "...\n\n";
}

// Get category
echo "4. Checking Category\n";
echo str_repeat("-", 50) . "\n";

$category = $categoryModel->where('id_category', $event['id_category'])->first();
if (!$category) {
    echo "⚠️  WARNING: Category not found\n\n";
} else {
    echo "✅ Category found:\n";
    echo "   - Title: " . $category['title'] . "\n";
    echo "   - ID: " . $category['id_category'] . "\n\n";
}

// Test notification payload
echo "5. Testing Notification Payload\n";
echo str_repeat("-", 50) . "\n";

$actionUser = [
    'fullname' => 'Test User',
    'id_user' => '999'
];

$titleNotif = "Event request join by " . $actionUser['fullname'];
$descNotif = $event['description'] . "\n#" . ($category['title'] ?? 'Unknown');
$image = $event['image'] ?? '';

$dataFcm = array(
    'title'   => $titleNotif,
    'body'    => $descNotif,
    "image"   => $image,
    'payload' => array(
        "keyname" => 'request_post',
        "post" => $event,
        "image"   => $image
    ),
);

echo "Notification payload:\n";
echo json_encode($dataFcm, JSON_PRETTY_PRINT) . "\n\n";

// Check if we can send
echo "6. Verification Result\n";
echo str_repeat("-", 50) . "\n";

if (!empty($install['token_fcm'])) {
    echo "✅ ALL CHECKS PASSED!\n";
    echo "   The notification SHOULD be sent to:\n";
    echo "   - User: " . $owner['fullname'] . " (" . $owner['email'] . ")\n";
    echo "   - Token: " . substr($install['token_fcm'], 0, 30) . "...\n\n";
    
    echo "To test sending manually, check the UserModel::sendFCMMessage() function\n";
    echo "Server Key: Check UserModel.php line 28\n\n";
} else {
    echo "❌ CANNOT SEND: No valid FCM token\n\n";
}

// Additional debugging info
echo "7. Additional Debug Info\n";
echo str_repeat("-", 50) . "\n";

// Check all users with this email
$allUsers = $userModel->where('email', $ownerEmail)->findAll();
echo "Users with email $ownerEmail: " . count($allUsers) . "\n";
foreach ($allUsers as $u) {
    echo "   - User ID: " . $u['id_user'] . " | Name: " . $u['fullname'] . " | Status: " . $u['status'] . "\n";
}
echo "\n";

// Check recent installs
$recentInstalls = $installModel->orderBy('date_updated', 'DESC')->findAll(5);
echo "Recent 5 installs:\n";
foreach ($recentInstalls as $inst) {
    echo "   - Install ID: " . $inst['id_install'] . " | OS: " . ($inst['os_platform'] ?? 'NULL') . 
         " | Token: " . (empty($inst['token_fcm']) ? 'EMPTY' : 'EXISTS') . 
         " | Updated: " . ($inst['date_updated'] ?? 'NULL') . "\n";
}

echo "\n=== END DEBUG ===\n";
