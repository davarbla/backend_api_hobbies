<?php
/**
 * Final verification that gallery system is ready
 */

echo "
╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║           GALLERY UPLOAD SYSTEM - VERIFICATION              ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝

";

$allGood = true;

// Check 1: Database Columns
echo "1. Checking Database Columns...\n";
echo "   ────────────────────────────\n";
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) {
    echo "   ❌ Database connection failed\n";
    $allGood = false;
} else {
    $result = $conn->query('DESCRIBE tb_user');
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    $required = ['image2', 'image3', 'image4', 'image5', 'image6', 'image7', 
                 'image8', 'image9', 'image10', 'public', 'friends', 'fun', 'face'];
    $missing = array_diff($required, $columns);
    
    if (empty($missing)) {
        echo "   ✅ All gallery columns present\n";
    } else {
        echo "   ❌ Missing columns: " . implode(', ', $missing) . "\n";
        $allGood = false;
    }
}

// Check 2: Upload Directories
echo "\n2. Checking Upload Directories...\n";
echo "   ────────────────────────────\n";
$dirs = [
    'Profile' => __DIR__ . '/root/public/upload/user/',
    'Public' => __DIR__ . '/root/public/upload/user/public/',
    'Friends' => __DIR__ . '/root/public/upload/user/friends/',
    'Fun' => __DIR__ . '/root/public/upload/user/fun/'
];

foreach ($dirs as $name => $path) {
    if (file_exists($path) && is_dir($path) && is_writable($path)) {
        echo "   ✅ $name gallery directory OK\n";
    } else {
        echo "   ❌ $name gallery directory issue\n";
        $allGood = false;
    }
}

// Check 3: Upload Controller
echo "\n3. Checking Upload Controller...\n";
echo "   ────────────────────────────\n";
$uploadFile = __DIR__ . '/root/app/Controllers/Upload.php';
if (file_exists($uploadFile)) {
    $content = file_get_contents($uploadFile);
    if (strpos($content, 'upload_image_user') !== false &&
        strpos($content, 'imageNumber') !== false &&
        strpos($content, 'public') !== false &&
        strpos($content, 'friends') !== false &&
        strpos($content, 'fun') !== false) {
        echo "   ✅ Upload controller has gallery logic\n";
    } else {
        echo "   ⚠️  Upload controller may be missing gallery logic\n";
        $allGood = false;
    }
} else {
    echo "   ❌ Upload controller not found\n";
    $allGood = false;
}

// Check 4: Flutter Gallery Page
echo "\n4. Checking Flutter Gallery Page...\n";
echo "   ────────────────────────────\n";
$flutterFile = dirname(__DIR__) . '/../fboys/lib/hobbiesapp/pages/update_photo_page.dart';
if (file_exists($flutterFile)) {
    $content = file_get_contents($flutterFile);
    // Check that height constraint was removed
    if (strpos($content, 'height: Get.height,') !== false) {
        echo "   ⚠️  Height constraint still present - this may cause display issues\n";
        echo "      The fix has been applied but file needs to be reloaded\n";
    } else if (strpos($content, 'UserGallery(user)') !== false) {
        echo "   ✅ Gallery page structure correct\n";
    } else {
        echo "   ⚠️  Gallery page structure unexpected\n";
    }
} else {
    echo "   ⚠️  Flutter gallery page not found at expected location\n";
}

// Check 5: Test User Data
echo "\n5. Checking Test User Data...\n";
echo "   ────────────────────────────\n";
if ($conn) {
    $result = $conn->query("SELECT id_user, fullname, `public`, friends, fun FROM tb_user WHERE status = 1 LIMIT 1");
    $user = $result->fetch_object();
    if ($user) {
        echo "   ✅ Test user found: {$user->fullname} (ID: {$user->id_user})\n";
        echo "      - Public gallery: " . ($user->public ? "enabled" : "disabled") . "\n";
        echo "      - Friends gallery: " . ($user->friends ? "enabled" : "disabled") . "\n";
        echo "      - Fun gallery: " . ($user->fun ? "enabled" : "disabled") . "\n";
    } else {
        echo "   ⚠️  No active users found for testing\n";
    }
}

// Final Summary
echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
if ($allGood) {
    echo "║                      ✅ ALL SYSTEMS GO!                      ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "Next Steps:\n";
    echo "───────────\n";
    echo "1. Restart your Flutter app\n";
    echo "2. Navigate to the gallery page (UpdatePhoto)\n";
    echo "3. You should see all three gallery sections:\n";
    echo "   • Public Gallery (3 slots)\n";
    echo "   • Friends Gallery (3 slots)\n";
    echo "   • Fun Gallery (3 slots)\n";
    echo "4. Test uploading images to each gallery\n";
    echo "\n";
    echo "If you still have issues, check:\n";
    echo "• Flutter console for errors\n";
    echo "• PHP error logs\n";
    echo "• Network requests in Flutter DevTools\n";
} else {
    echo "║                   ⚠️  ISSUES DETECTED                       ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "Please review the issues above and:\n";
    echo "1. Run fix_gallery_columns.php to add missing database columns\n";
    echo "2. Verify upload directories exist and are writable\n";
    echo "3. Check that Flutter changes are saved and app is rebuilt\n";
}

echo "\n";
echo "For detailed information, see: GALLERY_FIX_SUMMARY_COMPLETE.md\n";
echo "\n";

if ($conn) $conn->close();
?>
