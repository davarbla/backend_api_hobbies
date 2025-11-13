<?php
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Checking user categories...\n";
echo "==========================\n";

// Get the current install token
$token = 'eslFc4PmRGO_Newp-kkKYt:APA91bECQ8koWYKOs5GFmu3JiCo__w7SAcVvI_VAgV0WrKlB06ZUqZ6fc8caRaUp23TsjJNolepscYv_EcJGp3AHEusCiTqU5tj4igEZlO6FWPb2SXcb2W8';

// Find the install and user
$installResult = $conn->prepare("SELECT id_install FROM tb_install WHERE token_fcm = ?");
$installResult->bind_param('s', $token);
$installResult->execute();
$install = $installResult->get_result()->fetch_assoc();

if ($install) {
    $installId = $install['id_install'];
    
    // Find the user for this install
    $userResult = $conn->prepare("SELECT id_user FROM tb_user WHERE id_install = ?");
    $userResult->bind_param('i', $installId);
    $userResult->execute();
    $user = $userResult->get_result()->fetch_assoc();
    
    if ($user) {
        $userId = $user['id_user'];
        echo "User ID: $userId\n\n";
        
        // Check all categories for this user
        $sql = "SELECT uc.*, c.title, c.country 
                FROM tb_user_category uc 
                JOIN tb_category c ON uc.id_category = c.id_category 
                WHERE uc.id_user = ? 
                ORDER BY uc.date_created DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "Categories for user $userId:\n";
            while ($row = $result->fetch_assoc()) {
                echo "- {$row['title']} (ID: {$row['id_category']}, Country: {$row['country']}, Status: {$row['status']})\n";
            }
        } else {
            echo "No categories found for user $userId\n";
        }
        
        // Also check for any categories with id_user = 0
        echo "\nChecking for unassigned categories (id_user = 0):\n";
        $zeroResult = $conn->query("SELECT COUNT(*) as count FROM tb_user_category WHERE id_user = '0'");
        $zeroCount = $zeroResult->fetch_assoc()['count'];
        echo "Found $zeroCount categories with id_user = '0'\n";
    }
}

$conn->close();
?>
