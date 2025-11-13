<?php
// Check the current user setup
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Checking current user setup...\n";
echo "================================\n";

// Get the current install token
$token = 'eslFc4PmRGO_Newp-kkKYt:APA91bECQ8koWYKOs5GFmu3JiCo__w7SAcVvI_VAgV0WrKlB06ZUqZ6fc8caRaUp23TsjJNolepscYv_EcJGp3AHEusCiTqU5tj4igEZlO6FWPb2SXcb2W8';

// Find the install and user
$installResult = $conn->prepare("SELECT id_install FROM tb_install WHERE token_fcm = ?");
$installResult->bind_param('s', $token);
$installResult->execute();
$install = $installResult->get_result()->fetch_assoc();

if ($install) {
    $installId = $install['id_install'];
    echo "Install ID: $installId\n";
    
    // Find the user for this install
    $userResult = $conn->prepare("SELECT id_user, email, username FROM tb_user WHERE id_install = ?");
    $userResult->bind_param('i', $installId);
    $userResult->execute();
    $user = $userResult->get_result()->fetch_assoc();
    
    if ($user) {
        $userId = $user['id_user'];
        echo "User ID: $userId\n";
        echo "Email: {$user['email']}\n";
        echo "Username: {$user['username']}\n";
        
        // Check user categories
        $catResult = $conn->prepare("SELECT uc.*, c.title FROM tb_user_category uc JOIN tb_category c ON uc.id_category = c.id_category WHERE uc.id_user = ? AND uc.status = 1");
        $catResult->bind_param('i', $userId);
        $catResult->execute();
        $categories = $catResult->get_result();
        
        echo "\nUser Categories (status=1):\n";
        while ($cat = $categories->fetch_assoc()) {
            echo "- {$cat['title']} (Category ID: {$cat['id_category']}, User Category ID: {$cat['id_user_category']})\n";
        }
    } else {
        echo "❌ No user found for install ID: $installId\n";
    }
} else {
    echo "❌ Install not found\n";
}

$conn->close();
?>
