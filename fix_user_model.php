<?php
// Check and fix UserModel save method for gallery images
echo "Checking UserModel save method...\n";
echo "================================\n";

$userModelFile = __DIR__ . '/root/app/Models/UserModel.php';
if (file_exists($userModelFile)) {
    echo "✅ UserModel.php found\n";
    $content = file_get_contents($userModelFile);
    
    // Check if save method handles gallery images
    if (strpos($content, 'image2') !== false && strpos($content, 'image3') !== false) {
        echo "✅ UserModel contains gallery image references\n";
    } else {
        echo "❌ UserModel missing gallery image references\n";
    }
    
    // Check for proper column handling
    if (strpos($content, 'public') !== false && strpos($content, 'friends') !== false && strpos($content, 'fun') !== false) {
        echo "✅ UserModel contains gallery type columns\n";
    } else {
        echo "❌ UserModel missing gallery type columns\n";
    }
    
    echo "\nUserModel save method needs to handle:\n";
    echo "- image2, image3, image4 (public gallery)\n";
    echo "- image5, image6, image7 (friends gallery)\n";
    echo "- image8, image9, image10+ (fun gallery)\n";
    echo "- public, friends, fun flags\n";
    echo "- date_img_upd timestamp\n";
    
} else {
    echo "❌ UserModel.php not found at expected location\n";
}
?>
