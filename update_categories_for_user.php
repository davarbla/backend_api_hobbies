<?php
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Updating categories for current install...\n";
echo "==========================================\n";

// Get the current install token from the test
$token = 'eslFc4PmRGO_Newp-kkKYt:APA91bECQ8koWYKOs5GFmu3JiCo__w7SAcVvI_VAgV0WrKlB06ZUqZ6fc8caRaUp23TsjJNolepscYv_EcJGp3AHEusCiTqU5tj4igEZlO6FWPb2SXcb2W8';

// Find the install and user
$installResult = $conn->prepare("SELECT id_install FROM tb_install WHERE token_fcm = ?");
$installResult->bind_param('s', $token);
$installResult->execute();
$install = $installResult->get_result()->fetch_assoc();

if ($install) {
    $installId = $install['id_install'];
    echo "Found install ID: $installId\n";
    
    // Find the user for this install
    $userResult = $conn->prepare("SELECT id_user FROM tb_user WHERE id_install = ?");
    $userResult->bind_param('i', $installId);
    $userResult->execute();
    $user = $userResult->get_result()->fetch_assoc();
    
    if ($user) {
        $userId = $user['id_user'];
        echo "Found user ID: $userId\n";
        
        // Update categories with id_user = 0 to use the actual user ID
        $updateSql = "UPDATE tb_user_category 
                      SET id_user = ? 
                      WHERE id_user = '0'";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('i', $userId);
        
        if ($updateStmt->execute()) {
            $affected = $updateStmt->affected_rows;
            echo "✅ Updated $affected category records to user ID: $userId\n";
            
            // Show the updated categories
            $checkSql = "SELECT uc.*, c.title 
                         FROM tb_user_category uc 
                         JOIN tb_category c ON uc.id_category = c.id_category 
                         WHERE uc.id_user = ? 
                         LIMIT 5";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param('i', $userId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            echo "\nSample updated categories:\n";
            while ($row = $result->fetch_assoc()) {
                echo "- {$row['title']} (ID: {$row['id_category']}, Status: {$row['status']})\n";
            }
        } else {
            echo "❌ Error updating categories: " . $updateStmt->error . "\n";
        }
    } else {
        echo "❌ No user found for install ID: $installId\n";
    }
} else {
    echo "❌ Install not found for token: " . substr($token, 0, 20) . "...\n";
}

$conn->close();
?>
