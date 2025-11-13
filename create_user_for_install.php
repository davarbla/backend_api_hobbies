<?php
$conn = new mysqli('localhost', 'root', '', 'hobbies');
if ($conn->connect_error) die('Connection failed');

echo "Checking install and user records...\n";
echo "===================================\n";

// Get install records without users
$result = $conn->query("SELECT i.* FROM tb_install i 
                        LEFT JOIN tb_user u ON i.id_install = u.id_install 
                        WHERE u.id_user IS NULL 
                        ORDER BY i.id_install DESC");

if ($result->num_rows > 0) {
    echo "Found installs without users:\n";
    while ($install = $result->fetch_assoc()) {
        echo "- Install ID: {$install['id_install']}, Token: " . substr($install['token_fcm'], 0, 20) . "...\n";
        
        // Create a user for this install
        $userId = 'user_' . $install['id_install'] . '_' . time();
        $email = $userId . '@hobbies.local';
        $username = 'user_' . $install['id_install'];
        
        $sql = "INSERT INTO tb_user (id_install, uid_fcm, email, username, fullname, password_user, latitude, date_created, date_updated, status) 
                VALUES (?, ?, ?, ?, ?, '', '0,0', NOW(), NOW(), 1)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('issss', $install['id_install'], $userId, $email, $username, $username);
        
        if ($stmt->execute()) {
            echo "  ✅ Created user: $userId (email: $email)\n";
            
            // Get the actual user ID
            $newUserId = $conn->insert_id;
            
            // Update categories to use the new user ID
            $updateSql = "UPDATE tb_user_category 
                          SET id_user = ? 
                          WHERE id_user = '0'";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param('i', $newUserId);
            $updateStmt->execute();
            
            $affected = $updateStmt->affected_rows;
            if ($affected > 0) {
                echo "  ✅ Updated $affected category records to user ID: $newUserId\n";
            }
        } else {
            echo "  ❌ Error creating user: " . $stmt->error . "\n";
        }
        $stmt->close();
    }
} else {
    echo "All installs have corresponding users.\n";
}

// Show summary
echo "\nSummary:\n";
$result = $conn->query("SELECT COUNT(*) as total FROM tb_install");
$installs = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM tb_user");
$users = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM tb_user_category WHERE id_user != '0'");
$categories = $result->fetch_assoc()['total'];

echo "- Total installs: $installs\n";
echo "- Total users: $users\n";
echo "- Categories with users: $categories\n";

$conn->close();
?>
