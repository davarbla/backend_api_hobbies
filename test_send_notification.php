<?php
/**
 * Test script to manually send a notification to a specific user
 * This will attempt to send a real FCM notification
 */

// Include CodeIgniter bootstrap
require_once __DIR__ . '/root/app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';

use App\Models\UserModel;
use App\Models\InstallModel;

echo "=== FCM NOTIFICATION TEST ===\n\n";

// Test parameters - MODIFY THESE
$targetEmail = 'davarbla.g@gmail.com';
$testMessage = [
    'title' => 'Test Notification from Backend',
    'body' => 'This is a test to verify FCM is working',
    'image' => '',
    'payload' => [
        'keyname' => 'test',
        'message' => 'If you receive this, FCM is working!'
    ]
];

// Initialize models
$userModel = new UserModel();
$installModel = new InstallModel();

// Find user
echo "Looking for user: $targetEmail\n";
$user = $userModel->where('email', $targetEmail)->where('status', '1')->first();

if (!$user) {
    die("❌ ERROR: User not found or inactive\n");
}

echo "✅ User found: " . $user['fullname'] . " (ID: " . $user['id_user'] . ")\n";

// Get FCM token
if (empty($user['id_install'])) {
    die("❌ ERROR: User has no id_install\n");
}

$install = $installModel->where('id_install', $user['id_install'])->first();
if (!$install || empty($install['token_fcm'])) {
    die("❌ ERROR: No FCM token found for this user\n");
}

echo "✅ FCM Token found: " . substr($install['token_fcm'], 0, 30) . "...\n";
echo "   Token length: " . strlen($install['token_fcm']) . " characters\n\n";

// Send notification
echo "Sending notification...\n";
echo "Payload:\n" . json_encode($testMessage, JSON_PRETTY_PRINT) . "\n\n";

try {
    $result = $userModel->sendFCMMessage($install['token_fcm'], $testMessage);
    
    echo "Response from FCM:\n";
    print_r($result);
    echo "\n";
    
    if (isset($result['success']) && $result['success'] > 0) {
        echo "✅ SUCCESS! Notification sent successfully.\n";
        echo "   If the user doesn't receive it:\n";
        echo "   1. Check if notifications are enabled in device settings\n";
        echo "   2. Check if app is in battery optimization whitelist\n";
        echo "   3. Try force-stopping and restarting the app\n";
    } else if (isset($result['failure']) && $result['failure'] > 0) {
        echo "❌ FAILED! FCM rejected the notification.\n";
        if (isset($result['results'])) {
            echo "   Error details:\n";
            print_r($result['results']);
        }
        echo "\n   Common causes:\n";
        echo "   - Invalid or expired FCM token\n";
        echo "   - Wrong server key in UserModel.php\n";
        echo "   - Token not registered properly\n";
    } else {
        echo "⚠️  UNKNOWN RESPONSE from FCM\n";
    }
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
}

echo "\n=== END TEST ===\n";
