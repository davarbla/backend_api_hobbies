<?php
/**
 * Complete Notification Setup Verification
 * This checks EVERYTHING needed for notifications to work
 */

// Include CodeIgniter bootstrap
require_once __DIR__ . '/root/app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';

use App\Models\UserModel;
use App\Models\InstallModel;
use App\Models\PostModel;

$issues = [];
$warnings = [];
$passed = [];

echo "╔════════════════════════════════════════════════════════╗\n";
echo "║     NOTIFICATION SYSTEM VERIFICATION                   ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

// 1. Check Backend Code Fixes
echo "1. Checking Backend Code Fixes\n";
echo str_repeat("─", 56) . "\n";

$apiPhpContent = file_get_contents(__DIR__ . '/root/app/Controllers/Api.php');

// Check for the fixed categPost variable definition
if (strpos($apiPhpContent, '$categPost = $this->categModel->getById($idCateg);') !== false) {
    $passed[] = "✅ Api.php: categPost bug fixed";
} else {
    $issues[] = "❌ Api.php: categPost fix not found (undefined variable bug)";
}

// Check for null safety checks
if (substr_count($apiPhpContent, 'if (!empty($ownerUser[\'token_fcm\']))') > 0 ||
    substr_count($apiPhpContent, 'if (!empty($actionUser[\'token_fcm\']))') > 0) {
    $passed[] = "✅ Api.php: Null safety checks added";
} else {
    $warnings[] = "⚠️  Api.php: Null safety checks not found";
}

// Check Post.php
$postPhpContent = file_get_contents(__DIR__ . '/root/app/Controllers/Post.php');
if (strpos($postPhpContent, 'if (!empty($ownerUser[\'token_fcm\']))') !== false) {
    $passed[] = "✅ Post.php: Null safety check added";
} else {
    $warnings[] = "⚠️  Post.php: Null safety check not found";
}

echo implode("\n", $passed) . "\n";
if (!empty($warnings)) echo implode("\n", $warnings) . "\n";
if (!empty($issues)) echo implode("\n", $issues) . "\n";
echo "\n";

// 2. Check Database
echo "2. Checking Database Connection & Tables\n";
echo str_repeat("─", 56) . "\n";

$userModel = new UserModel();
$installModel = new InstallModel();
$postModel = new PostModel();

try {
    $db = \Config\Database::connect();
    echo "✅ Database connection successful\n";
    
    // Check tables exist
    $tables = ['tb_user', 'tb_install', 'tb_post', 'tb_category', 'tb_user_post'];
    foreach ($tables as $table) {
        if ($db->tableExists($table)) {
            echo "✅ Table '$table' exists\n";
        } else {
            $issues[] = "❌ Table '$table' missing";
        }
    }
} catch (Exception $e) {
    $issues[] = "❌ Database error: " . $e->getMessage();
}
echo "\n";

// 3. Check FCM Tokens
echo "3. Checking FCM Tokens in Database\n";
echo str_repeat("─", 56) . "\n";

try {
    $allUsers = $userModel->where('status', '1')->findAll();
    $totalUsers = count($allUsers);
    $usersWithTokens = 0;
    $usersWithoutTokens = 0;
    
    foreach ($allUsers as $user) {
        if (!empty($user['id_install'])) {
            $install = $installModel->where('id_install', $user['id_install'])->first();
            if ($install && !empty($install['token_fcm'])) {
                $usersWithTokens++;
            } else {
                $usersWithoutTokens++;
            }
        } else {
            $usersWithoutTokens++;
        }
    }
    
    echo "Total active users: $totalUsers\n";
    echo "✅ Users WITH valid FCM tokens: $usersWithTokens\n";
    
    if ($usersWithoutTokens > 0) {
        echo "❌ Users WITHOUT FCM tokens: $usersWithoutTokens\n";
        $issues[] = "CRITICAL: $usersWithoutTokens users don't have FCM tokens!";
        echo "\n   These users need to reinstall the app:\n";
        
        // Show which users need reinstall
        foreach ($allUsers as $user) {
            $hasToken = false;
            if (!empty($user['id_install'])) {
                $install = $installModel->where('id_install', $user['id_install'])->first();
                if ($install && !empty($install['token_fcm'])) {
                    $hasToken = true;
                }
            }
            
            if (!$hasToken) {
                echo "   - " . $user['email'] . " (" . $user['fullname'] . ")\n";
            }
        }
    } else {
        echo "✅ All users have FCM tokens!\n";
    }
} catch (Exception $e) {
    $issues[] = "❌ Error checking tokens: " . $e->getMessage();
}
echo "\n";

// 4. Check Firebase Server Key
echo "4. Checking Firebase Configuration\n";
echo str_repeat("─", 56) . "\n";

$userModelContent = file_get_contents(__DIR__ . '/root/app/Models/UserModel.php');
if (preg_match('/private\s+\$keyServerFCM\s*=\s*[\'"]([^\'"]+)[\'"];/', $userModelContent, $matches)) {
    $serverKey = $matches[1];
    echo "✅ Firebase Server Key found\n";
    echo "   Key: " . substr($serverKey, 0, 30) . "...\n";
    
    if (strlen($serverKey) > 100) {
        echo "✅ Server key length looks valid\n";
    } else {
        $warnings[] = "⚠️  Server key seems too short (might be invalid)";
    }
} else {
    $issues[] = "❌ Firebase Server Key not found in UserModel.php";
}
echo "\n";

// 5. Test Notification Function
echo "5. Checking sendFCMMessage Function\n";
echo str_repeat("─", 56) . "\n";

if (method_exists($userModel, 'sendFCMMessage')) {
    echo "✅ sendFCMMessage() function exists\n";
} else {
    $issues[] = "❌ sendFCMMessage() function not found";
}
echo "\n";

// 6. Check Recent Events
echo "6. Checking Recent Events (Posts)\n";
echo str_repeat("─", 56) . "\n";

try {
    $recentPosts = $postModel->orderBy('date_created', 'DESC')->findAll(5);
    echo "Recent events found: " . count($recentPosts) . "\n";
    
    if (count($recentPosts) > 0) {
        echo "\nRecent 5 events:\n";
        foreach ($recentPosts as $post) {
            echo "   - ID: " . $post['id_post'] . " | Title: " . 
                 (isset($post['title']) ? substr($post['title'], 0, 30) : 'N/A') . 
                 " | Owner: " . $post['id_user'] . "\n";
        }
    }
} catch (Exception $e) {
    $warnings[] = "⚠️  Could not fetch recent events: " . $e->getMessage();
}
echo "\n";

// Final Summary
echo "╔════════════════════════════════════════════════════════╗\n";
echo "║                    SUMMARY                             ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

if (empty($issues) && empty($warnings)) {
    echo "🎉 ALL CHECKS PASSED! 🎉\n\n";
    echo "Backend is configured correctly.\n";
    echo "If notifications still don't work:\n";
    echo "  1. Check if Flutter app has been rebuilt\n";
    echo "  2. Check if users have reinstalled the app\n";
    echo "  3. Check device notification settings\n";
    echo "  4. Run: php test_send_notification.php\n";
} else {
    if (!empty($issues)) {
        echo "❌ CRITICAL ISSUES FOUND:\n";
        foreach ($issues as $issue) {
            echo "   $issue\n";
        }
        echo "\n";
    }
    
    if (!empty($warnings)) {
        echo "⚠️  WARNINGS:\n";
        foreach ($warnings as $warning) {
            echo "   $warning\n";
        }
        echo "\n";
    }
    
    echo "Next steps:\n";
    echo "  1. Fix the issues above\n";
    echo "  2. If 'Users WITHOUT FCM tokens' > 0:\n";
    echo "     → Rebuild Flutter app\n";
    echo "     → Users must reinstall the app\n";
    echo "  3. Run this script again\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "For more help, see: TROUBLESHOOTING_NOTIFICATION.md\n";
echo "═══════════════════════════════════════════════════════════\n";
