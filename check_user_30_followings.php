<?php
// Check if user ID 30 (pitie) is following anyone
$host = 'localhost';
$dbname = 'hobbies';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $idUser = 30; // pitie
    
    echo "=== CHECKING USER ID $idUser (pitie) ===\n\n";
    
    // Check user details
    $stmt = $pdo->prepare("SELECT id_user, fullname, email, total_following, total_follower FROM tb_user WHERE id_user = ?");
    $stmt->execute([$idUser]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "User: {$user['fullname']}\n";
        echo "Email: {$user['email']}\n";
        echo "Total Following (from tb_user): {$user['total_following']}\n";
        echo "Total Followers (from tb_user): {$user['total_follower']}\n\n";
    }
    
    // Check actual follow records
    echo "=== CHECKING ACTUAL FOLLOW RECORDS ===\n\n";
    
    $stmt = $pdo->prepare("SELECT * FROM tb_follow WHERE id_user = ? ORDER BY date_created DESC");
    $stmt->execute([$idUser]);
    $followRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total follow records in tb_follow: " . count($followRecords) . "\n\n";
    
    if (count($followRecords) > 0) {
        foreach ($followRecords as $follow) {
            echo "Follow ID: {$follow['id_follow']}\n";
            echo "  Following user ID: {$follow['id_user_to']}\n";
            echo "  Status: {$follow['status']} " . ($follow['status'] == 1 ? '(ACTIVE)' : '(UNFOLLOWED)') . "\n";
            echo "  Flag: {$follow['flag']} " . ($follow['flag'] == 1 ? '(ACTIVE)' : '(DELETED)') . "\n";
            echo "  Created: {$follow['date_created']}\n";
            
            // Get the user being followed
            $stmt2 = $pdo->prepare("SELECT fullname FROM tb_user WHERE id_user = ?");
            $stmt2->execute([$follow['id_user_to']]);
            $followedUser = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($followedUser) {
                echo "  Following: {$followedUser['fullname']}\n";
            }
            echo "\n";
        }
    } else {
        echo "❌ NO FOLLOW RECORDS FOUND\n\n";
        echo "USER 'pitie' HAS NOT FOLLOWED ANYONE YET!\n\n";
    }
    
    // Show active followings (what the API returns)
    $stmt = $pdo->prepare("SELECT * FROM tb_follow WHERE id_user = ? AND status = 1 AND flag = 1");
    $stmt->execute([$idUser]);
    $activeFollowings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== ACTIVE FOLLOWINGS (status=1, flag=1) ===\n";
    echo "Count: " . count($activeFollowings) . "\n\n";
    
    if (count($activeFollowings) == 0) {
        echo "❌ NO ACTIVE FOLLOWINGS\n\n";
        echo "SOLUTION:\n";
        echo "1. In the app, go to 'Find Users' or 'All People'\n";
        echo "2. Find a user you want to follow\n";
        echo "3. Click the 'Follow' button on their profile\n";
        echo "4. Return to the home page\n";
        echo "5. The user should now appear in the 'Following' section\n\n";
        
        // Show other users available to follow
        echo "=== AVAILABLE USERS TO FOLLOW ===\n";
        $stmt = $pdo->query("SELECT id_user, fullname, email FROM tb_user WHERE id_user != $idUser AND status = 1 LIMIT 5");
        $otherUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($otherUsers as $u) {
            echo "- {$u['fullname']} (ID: {$u['id_user']}, Email: {$u['email']})\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";
