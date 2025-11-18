<?php
/**
 * Quick script to check notification readiness for all users
 * Shows which users have valid FCM tokens and which need to update their app
 */

require_once __DIR__ . '/root/app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';

use App\Models\UserModel;
use App\Models\InstallModel;

echo "=================================\n";
echo "NOTIFICATION READINESS CHECK\n";
echo "=================================\n\n";

$userModel = new UserModel();
$installModel = new InstallModel();

// Get all active users with their install info
$db = \Config\Database::connect();
$query = $db->query("
    SELECT 
        u.id_user,
        u.fullname,
        u.email,
        u.id_install,
        i.token_fcm,
        LENGTH(i.token_fcm) as token_length,
        i.date_updated as last_token_update,
        CASE 
            WHEN u.id_install IS NULL THEN '❌ NO INSTALL'
            WHEN i.token_fcm IS NULL THEN '❌ NULL TOKEN'
            WHEN i.token_fcm = '' THEN '❌ EMPTY TOKEN'
            WHEN LENGTH(i.token_fcm) < 100 THEN '⚠️ INVALID (too short)'
            ELSE '✅ READY'
        END as status
    FROM tb_user u
    LEFT JOIN tb_install i ON u.id_install = i.id_install
    WHERE u.status = '1'
    ORDER BY 
        CASE 
            WHEN i.token_fcm IS NOT NULL AND LENGTH(i.token_fcm) >= 100 THEN 1
            ELSE 2
        END,
        i.date_updated DESC
");

$users = $query->getResultArray();
$totalUsers = count($users);
$readyUsers = 0;
$needsUpdate = 0;

echo "SUMMARY\n";
echo "-------\n";
foreach ($users as $user) {
    if ($user['status'] === '✅ READY') {
        $readyUsers++;
    } else {
        $needsUpdate++;
    }
}

echo "Total Active Users: $totalUsers\n";
echo "Ready for Notifications: $readyUsers ✅\n";
echo "Need App Update: $needsUpdate ⚠️\n";
echo "Readiness: " . round(($readyUsers / $totalUsers) * 100, 1) . "%\n\n";

echo "USER DETAILS\n";
echo "============\n";
printf("%-5s %-25s %-30s %-20s %s\n", "ID", "Name", "Email", "Last Update", "Status");
echo str_repeat("-", 110) . "\n";

foreach ($users as $user) {
    $lastUpdate = $user['last_token_update'] 
        ? date('Y-m-d H:i', strtotime($user['last_token_update'])) 
        : 'Never';
    
    printf(
        "%-5s %-25s %-30s %-20s %s\n",
        $user['id_user'],
        substr($user['fullname'], 0, 24),
        substr($user['email'], 0, 29),
        $lastUpdate,
        $user['status']
    );
}

echo "\n\n";
echo "USERS NEEDING APP UPDATE\n";
echo "========================\n";

$needsUpdateList = array_filter($users, function($user) {
    return $user['status'] !== '✅ READY';
});

if (empty($needsUpdateList)) {
    echo "✅ All users are ready! No action needed.\n";
} else {
    echo "The following users need to update their app:\n\n";
    foreach ($needsUpdateList as $user) {
        echo "• {$user['fullname']} ({$user['email']}) - {$user['status']}\n";
    }
    
    echo "\n";
    echo "ACTIONS REQUIRED:\n";
    echo "-----------------\n";
    echo "1. Contact these users\n";
    echo "2. Ask them to update the app from Play Store / App Store\n";
    echo "3. OR ask them to uninstall and reinstall the app\n";
    echo "4. They must grant notification permission when prompted\n";
    echo "5. Rerun this script to verify\n";
}

echo "\n\n";
echo "RECENT TOKEN REGISTRATIONS\n";
echo "==========================\n";

$recentQuery = $db->query("
    SELECT 
        u.fullname,
        u.email,
        i.date_updated,
        TIMESTAMPDIFF(HOUR, i.date_updated, NOW()) as hours_ago
    FROM tb_user u
    JOIN tb_install i ON u.id_install = i.id_install
    WHERE u.status = '1' 
      AND i.token_fcm IS NOT NULL 
      AND LENGTH(i.token_fcm) >= 100
    ORDER BY i.date_updated DESC
    LIMIT 10
");

$recentUsers = $recentQuery->getResultArray();

if (empty($recentUsers)) {
    echo "No recent token registrations found.\n";
} else {
    foreach ($recentUsers as $user) {
        $timeAgo = $user['hours_ago'] < 1 
            ? "Less than 1 hour ago" 
            : "{$user['hours_ago']} hours ago";
        echo "• {$user['fullname']} ({$user['email']}) - $timeAgo\n";
    }
}

echo "\n";
echo "=================================\n";
echo "END OF REPORT\n";
echo "=================================\n";
